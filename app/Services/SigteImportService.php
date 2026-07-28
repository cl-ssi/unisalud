<?php

namespace App\Services;

use App\Models\Address;
use App\Models\ContactPoint;
use App\Models\Identifier;
use App\Models\SigteExternalWaitlistImport;
use App\Models\SigteExternalWaitlistRun;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

/**
 * Parses and imports the SIGTE "LE CNE" / "LE Quirúrgica" spreadsheet
 * uploads. Runs inside a queued job (large files can take minutes to
 * process), so it must not depend on any Livewire/Filament page state.
 */
class SigteImportService
{
    /**
     * Disk used to stage SIGTE uploads between the web request that receives
     * them and the queued job that processes them. Falls back to the local
     * disk in local development, where GCS credentials generally aren't
     * configured, so uploads don't silently fail writing to storage they
     * can't reach.
     */
    public static function uploadsDisk(): string
    {
        return app()->environment('local') ? 'local' : 'gcs';
    }

    /**
     * Streams a spreadsheet's rows one at a time via $onRow($headers, $rowValues),
     * supporting both .xlsx/.xls and .csv (semicolon-delimited, as commonly
     * exported locally) uploads.
     *
     * We use OpenSpout rather than PhpSpreadsheet here specifically because it
     * reads the file forward in a single pass and never materializes the whole
     * sheet in memory — PhpSpreadsheet builds a full in-memory object graph for
     * the entire file on load() (even with setReadDataOnly), which is what
     * pushed a 5000-row upload past the container's memory limit.
     *
     * The upload lives on the "gcs" disk rather than local storage: this runs
     * inside a queued job, in an HTTP request separate from (and potentially on
     * a different container instance than) the one that received the upload,
     * so the file has to be somewhere durable and shared, not on whichever
     * instance's local disk happened to write it. Both readers need a real
     * local path though, so we stream it down to a temp file first.
     */
    public function eachRow(string $filePath, callable $onRow): void
    {
        $isCsv = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'csv';
        $tempPath = tempnam(sys_get_temp_dir(), 'sigte_');

        try {
            $stream = Storage::disk(self::uploadsDisk())->readStream($filePath);
            file_put_contents($tempPath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            $reader = $isCsv ? new CsvReader($this->csvOptions($tempPath)) : new XlsxReader();
            $reader->open($tempPath);

            $headers = null;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $values = array_map($this->normalizeCellValue(...), $row->toArray());

                    if ($headers === null) {
                        $headers = array_map('trim', $values);

                        continue;
                    }

                    $onRow($headers, $values);
                }

                break; // SIGTE uploads only ever have one relevant sheet.
            }

            $reader->close();
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * The export is usually semicolon-delimited and sometimes Windows-1252
     * encoded (as commonly produced by local exports); sniff a small prefix
     * of the file rather than reading it all just to detect the encoding.
     */
    private function csvOptions(string $tempPath): CsvOptions
    {
        $options = new CsvOptions();
        $options->FIELD_DELIMITER = ';';
        $options->FIELD_ENCLOSURE = '"';

        $handle = fopen($tempPath, 'r');
        $sample = $handle !== false ? fread($handle, 65536) : '';
        if ($handle !== false) {
            fclose($handle);
        }

        if ($sample !== '' && $sample !== false && ! mb_check_encoding($sample, 'UTF-8')) {
            $options->ENCODING = 'Windows-1252';
        }

        return $options;
    }

    private function normalizeCellValue(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            // The export stores dates as US-style m/d/Y text (e.g. "1/25/2004");
            // match that format in the rare case a cell is a native date type.
            return $value->format('n/j/Y');
        }

        return trim((string) ($value ?? ''));
    }

    /**
     * Pads/truncates a row to exactly match the header count. XLSX rows are
     * always already this width, but a short CSV line (fewer trailing fields
     * than the header row) is common in these exports and should still be
     * imported with the missing fields blank, not dropped entirely.
     */
    private function alignRowToHeaders(array $rowData, array $headers): array
    {
        return array_slice(array_pad($rowData, count($headers), ''), 0, count($headers));
    }

    public function processLeCne(string $filePath): array
    {
        $nuevos = $actualizados = $errores = 0;
        $sawDataRow = false;
        $fileHeaders = [];
        $seenRuns = [];
        $inFileDuplicates = [];
        $preExisting = [];

        $this->eachRow($filePath, function (array $headers, array $rowData) use (
            &$nuevos, &$actualizados, &$errores, &$sawDataRow,
            &$fileHeaders, &$seenRuns, &$inFileDuplicates, &$preExisting
        ) {
            $sawDataRow = true;
            $fileHeaders = $headers;

            if (! array_filter($rowData, fn ($v) => $v !== '')) {
                return;
            }

            $data = array_combine($headers, $this->alignRowToHeaders($rowData, $headers));
            $run = preg_replace('/[^0-9]/', '', $data['RUN'] ?? '');
            if (! $run) {
                return;
            }

            try {
                [$isNew] = $this->upsertPatient($run, $data);
                if ($isNew) {
                    $nuevos++;
                } else {
                    $actualizados++;
                    // Already had an identifier: either this RUN repeats
                    // earlier in this same file, or it was already a patient
                    // before this upload started.
                    if (isset($seenRuns[$run])) {
                        $inFileDuplicates[] = $data;
                    } else {
                        $preExisting[] = $data;
                    }
                }
            } catch (\Exception) {
                $errores++;
            }

            $seenRuns[$run] = true;
        });

        if (! $sawDataRow) {
            return ['empty' => true];
        }

        $total = $nuevos + $actualizados;

        $this->saveDuplicatesFile('lecne', [
            ['title' => 'Duplicados en archivo', 'headers' => $fileHeaders, 'rows' => $inFileDuplicates],
            ['title' => 'Ya existian antes', 'headers' => $fileHeaders, 'rows' => $preExisting],
        ]);

        $this->saveMeta('lecne', [
            'estado'             => 'listo',
            'total'              => $total,
            'nuevos'             => $nuevos,
            'actualizados'       => $actualizados,
            'errores'            => $errores,
            'duplicados_archivo' => count($inFileDuplicates),
            'duplicados_previos' => count($preExisting),
            'fecha'              => now()->format('d-m-Y H:i'),
            'archivo'            => basename($filePath),
        ]);

        return compact('total', 'nuevos', 'actualizados', 'errores');
    }

    public function processLeQx(string $filePath, ?int $uploadedBy): array
    {
        $records = [];
        $sawDataRow = false;
        $fileHeaders = [];
        $runCounts = [];

        $this->eachRow($filePath, function (array $headers, array $rowData) use (&$records, &$sawDataRow, &$fileHeaders, &$runCounts) {
            $sawDataRow = true;
            $fileHeaders = $headers;

            if (! array_filter($rowData, fn ($v) => $v !== '')) {
                return;
            }

            $data = array_combine($headers, $this->alignRowToHeaders($rowData, $headers));
            $run = preg_replace('/[^0-9]/', '', $data['RUN'] ?? '');
            if ($run) {
                // Later rows for the same RUN overwrite earlier ones. We
                // deliberately don't keep every raw row around to build the
                // duplicates report — on a file with a large repeated RUN,
                // that doubled memory use enough to OOM the queue worker.
                $records[$run] = $data;
                $runCounts[$run] = ($runCounts[$run] ?? 0) + 1;
            }
        });

        if (! $sawDataRow) {
            return ['empty' => true];
        }

        $total = count($records);

        // One row per duplicated RUN (the last value that actually gets
        // saved below), annotated with how many times it repeated.
        $countHeader = 'VECES_EN_ARCHIVO';
        $inFileDuplicates = [];
        foreach ($runCounts as $run => $count) {
            if ($count > 1) {
                $inFileDuplicates[] = $records[$run] + [$countHeader => (string) $count];
            }
        }

        // RUNs from the previous LE Quirúrgica upload that also appear in
        // this one, captured before that previous snapshot gets replaced.
        $preExistingRuns = SigteExternalWaitlistRun::whereIn('run', array_keys($records))->pluck('run')->all();
        $preExisting = array_values(array_intersect_key($records, array_flip($preExistingRuns)));

        $this->saveDuplicatesFile('leqx', [
            ['title' => 'Duplicados en archivo', 'headers' => [...$fileHeaders, $countHeader], 'rows' => $inFileDuplicates],
            ['title' => 'Ya existian antes', 'headers' => $fileHeaders, 'rows' => $preExisting],
        ]);

        DB::transaction(function () use ($records, $total, $filePath, $uploadedBy) {
            // A new upload fully replaces the previous one: this is a
            // point-in-time snapshot of another specialty's waitlist,
            // not a cumulative record, so stale entries shouldn't linger.
            SigteExternalWaitlistImport::query()->delete();

            $import = SigteExternalWaitlistImport::create([
                'uploaded_by' => $uploadedBy,
                'filename'    => basename($filePath),
                'total_count' => $total,
            ]);

            $now = now();
            foreach (array_chunk($records, 500, true) as $chunk) {
                SigteExternalWaitlistRun::insert(array_map(fn ($run, $data) => [
                    'sigte_external_waitlist_import_id' => $import->id,
                    'run'                                => $run,
                    'data'                               => json_encode($data),
                    'created_at'                         => $now,
                ], array_keys($chunk), $chunk));
            }
        });

        $this->saveMeta('leqx', [
            'estado'             => 'listo',
            'total'              => $total,
            'duplicados_archivo' => count($inFileDuplicates),
            'duplicados_previos' => count($preExisting),
            'fecha'              => now()->format('d-m-Y H:i'),
            'archivo'            => basename($filePath),
        ]);

        return compact('total');
    }

    private function upsertPatient(string $run, array $data): array
    {
        $identifier = Identifier::where('value', $run)
            ->where('cod_con_identifier_type_id', 1)
            ->first();

        $sexMap = ['1' => 'male', '2' => 'female', '3' => 'other', '9' => 'unknown'];
        $sex = $sexMap[$data['SEXO'] ?? ''] ?? null;

        $birthday = null;
        if (! empty($data['FECHA_NAC'])) {
            try {
                // The export stores dates as US-style m/d/Y, e.g. "1/25/2004".
                $birthday = Carbon::createFromFormat('n/j/Y', $data['FECHA_NAC'])->format('Y-m-d');
            } catch (\Exception) {
            }
        }

        if (! $identifier?->user_id) {
            $given         = $data['NOMBRES'] ?: null;
            $fathersFamily = $data['PRIMER_APELLIDO'] ?: null;
            $mothersFamily = $data['SEGUNDO_APELLIDO'] ?: null;

            $user = User::create([
                'active'         => 1,
                'given'          => $given,
                'fathers_family' => $fathersFamily,
                'mothers_family' => $mothersFamily,
                'text'           => trim("{$given} {$fathersFamily} {$mothersFamily}"),
                'birthday'       => $birthday,
                'sex'            => $sex,
            ]);

            Identifier::create([
                'user_id'                    => $user->id,
                'use'                        => 'official',
                'cod_con_identifier_type_id' => 1,
                'value'                      => $run,
                'dv'                         => $data['DV'] ?: null,
            ]);

            $isNew = true;
        } else {
            $user = User::find($identifier->user_id);

            $updates = array_filter([
                'given'          => $user->given          ?: ($data['NOMBRES'] ?: null),
                'fathers_family' => $user->fathers_family ?: ($data['PRIMER_APELLIDO'] ?: null),
                'mothers_family' => $user->mothers_family ?: ($data['SEGUNDO_APELLIDO'] ?: null),
                'birthday'       => $user->birthday       ?: $birthday,
                'sex'            => $user->sex            ?: $sex,
                'text'           => $user->text           ?: trim(($data['NOMBRES'] ?? '') . ' ' . ($data['PRIMER_APELLIDO'] ?? '') . ' ' . ($data['SEGUNDO_APELLIDO'] ?? '')),
            ]);

            if ($updates) {
                $user->update($updates);
            }

            $isNew = false;
        }

        $viaMap = ['1' => 'calle', '2' => 'pasaje', '3' => 'avenida', '4' => 'otro'];
        if (! empty($data['NOM_CALLE'])) {
            Address::updateOrCreate(
                ['user_id' => $user->id, 'use' => 'home'],
                array_filter([
                    'type'     => 'physical',
                    'text'     => $data['NOM_CALLE'] ?: null,
                    'line'     => $data['NUM_DIRECCION'] ?: null,
                    'suburb'   => $data['RESTO_DIRECCION'] ?: null,
                    'city'     => $data['CIUDAD'] ?: null,
                    'is_rural' => ($data['COND_RURALIDAD'] ?? '') === '2',
                    'via'      => $viaMap[$data['VIA_DIRECCION'] ?? ''] ?? null,
                ])
            );
        }

        foreach ([['phone', 'home', 'FONO_FIJO'], ['phone', 'mobile', 'FONO_MOVIL']] as [$sys, $use, $col]) {
            $value = $data[$col] ?? '';
            if ($value && $value !== '0') {
                ContactPoint::updateOrCreate(
                    ['user_id' => $user->id, 'system' => $sys, 'use' => $use],
                    ['value' => $value]
                );
            }
        }

        $email = $data['EMAIL'] ?? '';
        if ($email && ! in_array($email, ['sinmail@sinmail.com', 'sin_correo@sinmail.cl', 'sin_correo@sinmail.com'])) {
            ContactPoint::updateOrCreate(
                ['user_id' => $user->id, 'system' => 'email', 'use' => 'work'],
                ['value' => $email]
            );
        }

        return [$isNew, $user];
    }

    /**
     * Marks $key as currently being imported, keeping the previous run's
     * totals in place so the page can still show them alongside the
     * "processing" banner until the new run finishes (or fails).
     */
    public function markProcessing(string $key, string $filename): void
    {
        $this->saveMeta($key, array_merge($this->readMeta($key), [
            'estado'       => 'procesando',
            'archivo'      => $filename,
            'fecha_inicio' => now()->format('d-m-Y H:i'),
        ]));
    }

    /**
     * Marks $key's most recent import attempt as failed, without discarding
     * the last successful run's totals.
     */
    public function markFailed(string $key, string $message): void
    {
        $this->saveMeta($key, array_merge($this->readMeta($key), [
            'estado'      => 'error',
            'mensaje'     => $message,
            'fecha_error' => now()->format('d-m-Y H:i'),
        ]));
    }

    public function saveMeta(string $key, array $data): void
    {
        Storage::disk('local')->makeDirectory('sigte');
        Storage::disk('local')->put("sigte/{$key}-meta.json", json_encode($data));
    }

    public function readMeta(string $key): array
    {
        $raw = Storage::disk('local')->get("sigte/{$key}-meta.json");

        return $raw ? (json_decode($raw, true) ?? []) : [];
    }

    /**
     * Path (on the "local" disk) of the duplicates report for $key's most
     * recent import — a two-sheet Excel of rows whose RUN repeats within the
     * uploaded file, and rows whose RUN already existed before the upload.
     */
    public function duplicatesPath(string $key): string
    {
        return "sigte/{$key}-duplicados.xlsx";
    }

    /**
     * @param  array<int, array{title: string, headers: array<string>, rows: array<int, array<string, string>>}>  $sheets
     */
    private function saveDuplicatesFile(string $key, array $sheets): void
    {
        $path = $this->duplicatesPath($key);
        $disk = Storage::disk('local');

        $hasRows = array_sum(array_map(fn (array $s) => count($s['rows']), $sheets)) > 0;

        if (! $hasRows) {
            $disk->delete($path);

            return;
        }

        $disk->makeDirectory('sigte');
        $this->writeDuplicatesWorkbook($disk->path($path), $sheets);
    }

    /**
     * Writes the duplicates report with OpenSpout rather than Maatwebsite
     * Excel/PhpSpreadsheet: PhpSpreadsheet builds a full in-memory cell
     * object graph per sheet, which is what OOM'd the queue worker on a LE
     * Quirúrgica file with a large duplicate set. OpenSpout streams rows
     * straight to disk, matching the memory-conscious approach eachRow()
     * already uses for reading.
     *
     * @param  array<int, array{title: string, headers: array<string>, rows: array<int, array<string, string>>}>  $sheets
     */
    private function writeDuplicatesWorkbook(string $absolutePath, array $sheets): void
    {
        $writer = new XlsxWriter();
        $writer->openToFile($absolutePath);

        $boldStyle = (new Style())->setFontBold();

        foreach ($sheets as $index => $sheet) {
            if ($index > 0) {
                $writer->addNewSheetAndMakeItCurrent();
            }

            $writer->getCurrentSheet()->setName($sheet['title']);
            $writer->addRow(Row::fromValues($sheet['headers'], $boldStyle));

            foreach ($sheet['rows'] as $row) {
                $writer->addRow(Row::fromValues(
                    array_map(fn (string $h) => (string) ($row[$h] ?? ''), $sheet['headers'])
                ));
            }
        }

        $writer->close();
    }
}
