<?php

namespace App\Filament\Imports;

use App\Imports\DemandManagementWaitlistImport;
use App\Imports\RowRejectedException;
use App\Models\DemandManagementWaitlist;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

/**
 * Gestión Demanda CSV upload through Filament's Import action. The file is
 * split into small queued chunks; Filament runs the chunks of one upload one
 * at a time (WithoutOverlapping on the import), so no chunk outlives a Cloud
 * Tasks request and the same patient is never created by two chunks at once.
 * Progress is kept on the import (processed_rows / total_rows), which the
 * DemandManagementImportProgress widget shows.
 *
 * Each row goes through DemandManagementWaitlistImport, the same rules as the
 * .xlsx upload; rows left out land in Filament's failed rows file.
 */
class DemandManagementWaitlistImporter extends Importer
{
    protected static ?string $model = DemandManagementWaitlist::class;

    /** importCsvRow() outcome => counter column on the imports table. */
    protected const OUTCOME_COLUMNS = [
        DemandManagementWaitlistImport::ROW_CREATED   => 'created_rows',
        DemandManagementWaitlistImport::ROW_UPDATED   => 'updated_rows',
        DemandManagementWaitlistImport::ROW_UNCHANGED => 'unchanged_rows',
    ];

    /** Row diagnosis => counter column on the imports table. */
    protected const DIAGNOSIS_COLUMNS = [
        DemandManagementWaitlistImport::DIAGNOSIS_GES     => 'ges_diagnosis_rows',
        DemandManagementWaitlistImport::DIAGNOSIS_NON_GES => 'non_ges_diagnosis_rows',
        DemandManagementWaitlistImport::DIAGNOSIS_NONE    => 'no_diagnosis_rows',
    ];

    /** One per queued chunk: its lookups are loaded once for the chunk. */
    protected ?DemandManagementWaitlistImport $rowImport = null;

    /**
     * No column mapping: the rows are read by their SIGTE header names.
     */
    public static function getColumns(): array
    {
        return [];
    }

    public function __invoke(array $data): void
    {
        $this->originalData = $data;

        if (! $this->rowImport) {
            DemandManagementWaitlistImport::forgetCaches();
            $this->rowImport = new DemandManagementWaitlistImport();
        }

        try {
            $outcome = $this->rowImport->importCsvRow($data);

            // Kept on the import (what the row did + its diagnosis): the chunks
            // are separate jobs. Inside the row's transaction, so a row that
            // fails afterwards is not counted.
            $counters = array_fill_keys(array_filter([
                self::OUTCOME_COLUMNS[$outcome] ?? null,
                self::DIAGNOSIS_COLUMNS[$this->rowImport->lastRowDiagnosis()] ?? null,
            ]), 1);
            if ($counters) {
                Import::query()->whereKey($this->import->getKey())->incrementEach($counters);
            }
        } catch (RowRejectedException $e) {
            throw new RowImportFailedException(implode(' | ', $e->reasons));
        } catch (\Throwable $e) {
            // Recorded as a failed row instead of failing (and retrying) the whole chunk.
            Log::error('DemandManagementWaitlistImporter: error en fila', [
                'import_id' => $this->import->getKey(),
                'sigte_id'  => $data['SIGTE_ID'] ?? null,
                'error'     => $e->getMessage(),
            ]);

            throw new RowImportFailedException('Error: ' . $e->getMessage());
        }
    }

    /**
     * In production a Cloud Tasks queue with max concurrent dispatches = 1
     * (WAITLIST_IMPORT_QUEUE): the chunks are then sent one after the other.
     * On a shared queue Cloud Tasks would send all of them at once.
     */
    public function getJobQueue(): ?string
    {
        return config('queue.waitlist_import_queue');
    }

    /**
     * Filament's default, plus a delay before a chunk that finds another
     * chunk of the same upload running is tried again; without it Cloud
     * Tasks redelivers the waiting chunks immediately, over and over.
     */
    public function getJobMiddleware(): array
    {
        return [
            (new WithoutOverlapping("import{$this->import->getKey()}"))->releaseAfter(30)->expireAfter(600),
        ];
    }

    public static function getCompletedNotificationTitle(Import $import): string
    {
        return 'Gestión Demanda: importación finalizada';
    }

    /**
     * e.g. "20.000 filas: 19.654 casos nuevos · 237 sin cambios (el caso ya
     * existía con los mismos datos, p. ej. fila repetida en el archivo) · 109 rechazadas."
     */
    public static function getCompletedNotificationBody(Import $import): string
    {
        $n = fn (int $value) => number_format($value, 0, ',', '.');
        $import->refresh(); // counters are incremented by the chunk jobs

        $created = (int) $import->created_rows;
        $updated = (int) $import->updated_rows;
        $unchanged = (int) $import->unchanged_rows;
        $failed = $import->getFailedRowsCount();
        // Successful rows that did nothing: fully empty lines.
        $empty = max(0, $import->successful_rows - $created - $updated - $unchanged);

        $count = fn (int $value, string $one, string $many) => $n($value) . ' ' . ($value === 1 ? $one : $many);

        $parts = array_filter([
            $created ? $count($created, 'caso nuevo', 'casos nuevos') : null,
            $updated ? $count($updated, 'actualizado', 'actualizados') : null,
            $unchanged ? $n($unchanged) . ' sin cambios (el caso ya existía con los mismos datos, p. ej. fila repetida en el archivo)' : null,
            $empty ? $count($empty, 'fila vacía', 'filas vacías') : null,
            $failed ? $count($failed, 'rechazada', 'rechazadas') : null,
        ]);

        $body = $n($import->total_rows) . ' filas: ' . ($parts ? implode(' · ', $parts) : 'sin filas importadas') . '.';

        // CIE-10 of the imported rows, from the CIE-10 column or the diagnosis text.
        $diagnosis = array_filter([
            (int) $import->ges_diagnosis_rows ? $count((int) $import->ges_diagnosis_rows, 'con diagnóstico GES', 'con diagnóstico GES') : null,
            (int) $import->non_ges_diagnosis_rows ? $count((int) $import->non_ges_diagnosis_rows, 'con CIE-10 no GES', 'con CIE-10 no GES') : null,
            (int) $import->no_diagnosis_rows ? $count((int) $import->no_diagnosis_rows, 'sin CIE-10', 'sin CIE-10') : null,
        ]);
        if ($diagnosis) {
            $body .= ' Diagnóstico: ' . implode(' · ', $diagnosis) . '.';
        }

        if ($failed) {
            $body .= ' Descarga el archivo de rechazadas para ver el motivo de cada una.';
        }

        return $body;
    }
}
