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
            $this->rowImport->importCsvRow($data);
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

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = number_format($import->successful_rows, 0, ',', '.') . ' de ' . number_format($import->total_rows, 0, ',', '.') . ' filas importadas.';

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failed, 0, ',', '.') . ' rechazadas: descarga el archivo para ver el motivo de cada una.';
        }

        return $body;
    }
}
