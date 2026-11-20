<?php

namespace App\Imports;

use App\Models\DemandManagementSpeciality;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

/**
 * Loads the Gestión Demanda Código MAI catalog from "Prestaciones_c-fechavigencia.xlsx"
 * (hoja "prest total"): code = Código MAI, text = Especialidad, name = Glosa.
 *
 * - The sheet named "prest total" is used when present; otherwise the first
 *   sheet whose heading row has the required columns.
 * - The heading row is found by name within the first rows, so title rows
 *   above it and column order do not matter.
 * - Every Tipo is loaded, with its TIPO_PREST in `type` (only type 4 rows are
 *   matched for TIPO_PREST 4 waitlist rows, see DemandManagementSpeciality::forCode).
 *   A sheet without a Tipo column is taken as all Tipo 4.
 * - Upsert by (Tipo, Código MAI): new codes are inserted, changed ones updated
 *   and soft-deleted ones restored. Codes missing from the file are left as
 *   they are (waitlists may point to them).
 *
 * Writes are batched (one read, chunked inserts, one update per changed row)
 * so the import stays fast against a remote database.
 */
class DemandManagementSpecialityCatalogImport
{
    public const PREFERRED_SHEET = 'prest total';

    /** Rows scanned looking for the heading row. */
    protected const HEADING_SCAN_ROWS = 15;

    protected const INSERT_CHUNK = 500;

    /**
     * Accepted (normalized) headings per field, most specific first.
     */
    protected const HEADINGS = [
        'code'      => ['codigo mai', 'cod mai', 'codigo prestacion mai', 'presta min', 'codigo'],
        'name'      => ['glosa', 'glosa prestacion', 'descripcion'],
        'specialty' => ['especialidad'],
        'type'      => ['tipo prest', 'tipo prestacion', 'tipo'],
    ];

    protected const REQUIRED = ['code', 'name', 'specialty'];

    public array $summary = [
        'sheet'      => null,
        'read'       => 0,  // data rows read from the sheet
        'by_type'    => [], // rows loaded per Tipo
        'empty'      => 0,  // skipped: no Código MAI
        'duplicate'  => 0,  // skipped: Tipo + Código MAI repeated in the file (first one kept)
        'created'    => 0,
        'updated'    => 0,
        'restored'   => 0,
        'unchanged'  => 0,
    ];

    public function __construct(protected string $path) {}

    /**
     * @throws \RuntimeException when no sheet has the required columns.
     */
    public function run(): array
    {
        $rows = $this->readRows();
        $this->save($rows);

        DemandManagementWaitlistImport::forgetCaches();

        return $this->summary;
    }

    /**
     * @return array<string, array{type: ?string, code: string, text: string, name: string}> keyed by "type|code"
     */
    protected function readRows(): array
    {
        $reader = new XlsxReader();
        $reader->open($this->path);

        try {
            $sheets = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                $sheets[] = $sheet;
            }

            // "prest total" first, then the rest in workbook order.
            usort($sheets, fn ($a, $b) => (int) ! $this->isPreferredSheet($a->getName()) <=> (int) ! $this->isPreferredSheet($b->getName()));

            foreach ($sheets as $sheet) {
                $columns = null;
                $rows = [];
                $rowNumber = 0;

                foreach ($sheet->getRowIterator() as $sheetRow) {
                    $values = array_map(fn ($value) => $this->cellToString($value), $sheetRow->toArray());
                    $rowNumber++;

                    if ($columns === null) {
                        $columns = $this->matchHeadings($values);
                        if ($columns === null && $rowNumber >= self::HEADING_SCAN_ROWS) {
                            continue 2; // not this sheet
                        }
                        continue;
                    }

                    $this->collectRow($values, $columns, $rows);
                }

                if ($columns !== null) {
                    $this->summary['sheet'] = $sheet->getName();

                    return $rows;
                }
            }
        } finally {
            $reader->close();
        }

        throw new \RuntimeException(
            'No se encontró una hoja con las columnas "Código MAI", "Glosa" y "Especialidad" '
            . '(se busca primero la hoja "' . self::PREFERRED_SHEET . '").'
        );
    }

    protected function collectRow(array $values, array $columns, array &$rows): void
    {
        $get = fn (string $field) => isset($columns[$field]) ? trim($values[$columns[$field]] ?? '') : '';

        $code = $get('code');
        $name = $get('name');
        $specialty = $get('specialty');

        if ($code === '' && $name === '' && $specialty === '') {
            return; // blank row
        }

        $this->summary['read']++;

        if ($code === '') {
            $this->summary['empty']++;

            return;
        }

        $type = isset($columns['type'])
            ? ($this->normalizeType($get('type')) ?: null)
            : DemandManagementSpeciality::ENTRY_TYPE_CODE;
        $key = "{$type}|{$code}";

        if (isset($rows[$key])) {
            $this->summary['duplicate']++;

            return;
        }

        $rows[$key] = ['type' => $type, 'code' => $code, 'text' => $specialty, 'name' => $name];
        $typeLabel = $type ?? 'sin Tipo';
        $this->summary['by_type'][$typeLabel] = ($this->summary['by_type'][$typeLabel] ?? 0) + 1;
    }

    /**
     * @param array<string, array{type: ?string, code: string, text: string, name: string}> $rows
     */
    protected function save(array $rows): void
    {
        $existing = DemandManagementSpeciality::withTrashed()
            ->whereNotNull('code')
            ->get(['id', 'type', 'code', 'text', 'name', 'deleted_at'])
            ->keyBy(fn ($s) => "{$s->type}|" . trim($s->code));

        $now = now();
        $inserts = [];
        $updates = [];

        foreach ($rows as $key => $row) {
            $current = $existing[$key] ?? null;

            if (! $current) {
                $inserts[] = $row + ['created_at' => $now, 'updated_at' => $now];

                continue;
            }

            $restored = $current->deleted_at !== null;
            $changed = $current->text !== $row['text'] || $current->name !== $row['name'];

            if (! $restored && ! $changed) {
                $this->summary['unchanged']++;

                continue;
            }

            $updates[$current->id] = [
                'text'       => $row['text'],
                'name'       => $row['name'],
                'deleted_at' => null,
                'updated_at' => $now,
            ];
            $this->summary[$restored ? 'restored' : 'updated']++;
        }

        DB::transaction(function () use ($inserts, $updates) {
            foreach (array_chunk($inserts, self::INSERT_CHUNK) as $chunk) {
                DB::table('demand_management_specialities')->insert($chunk);
            }

            foreach ($updates as $id => $values) {
                DB::table('demand_management_specialities')->where('id', $id)->update($values);
            }
        });

        $this->summary['created'] = count($inserts);
    }

    /**
     * Column index per field, or null when $values is not the heading row.
     */
    protected function matchHeadings(array $values): ?array
    {
        $normalized = array_map(fn ($v) => $this->normalizeHeading($v), $values);
        $columns = [];

        foreach (self::HEADINGS as $field => $candidates) {
            foreach ($candidates as $candidate) {
                $index = array_search($candidate, $normalized, true);
                if ($index !== false && ! in_array($index, $columns, true)) {
                    $columns[$field] = $index;
                    break;
                }
            }
        }

        return array_diff(self::REQUIRED, array_keys($columns)) ? null : $columns;
    }

    protected function normalizeHeading(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($value))));
    }

    /** "4", "4.0", "Tipo 4" → "4". */
    protected function normalizeType(string $value): string
    {
        return preg_match('/\d+/', $value, $m) ? (string) (int) $m[0] : '';
    }

    protected function isPreferredSheet(string $name): bool
    {
        return $this->normalizeHeading($name) === self::PREFERRED_SHEET;
    }

    protected function cellToString(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }
}
