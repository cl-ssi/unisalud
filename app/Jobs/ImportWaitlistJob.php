<?php

namespace App\Jobs;

use App\Exports\ImportReportExport;
use App\Imports\DemandManagementWaitlistImport;
use App\Imports\OdontologyWaitlistImport;
use App\Models\User;
use App\Services\SigteImportService;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Runs a Gestión Demanda / Odontología waitlist upload in the background
 * (a full SIGTE file takes longer than max_execution_time) and sends the
 * uploader a database notification with the summary and the result files.
 */
class ImportWaitlistJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /** A 50,000 row file takes around 15 minutes. config/queue.php retry_after must exceed this. */
    public int $timeout = 3600;

    public int $tries = 1;

    /**
     * @param class-string<DemandManagementWaitlistImport|OdontologyWaitlistImport> $importClass
     * @param string $filePath path on the uploads disk, shared with the workers; deleted when done
     * @param string $filenamePrefix prefix of the result files, e.g. "gestion-demanda-"
     */
    public function __construct(
        public string $importClass,
        public string $filePath,
        public int $uploadedBy,
        public string $title,
        public string $filenamePrefix,
    ) {
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->uploadedBy);
        // The import records the uploader on each event, and the result
        // files are stored and served per user.
        Auth::setUser($user);

        $import = new ($this->importClass)();
        // The imports need a real path, and pick the reader by its extension.
        $localPath = tempnam(sys_get_temp_dir(), 'waitlist-import-') . '.' . pathinfo($this->filePath, PATHINFO_EXTENSION);

        try {
            $stream = Storage::disk(SigteImportService::uploadsDisk())->readStream($this->filePath);
            file_put_contents($localPath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            $import->import($localPath);
        } finally {
            @unlink($localPath);
            Storage::disk(SigteImportService::uploadsDisk())->delete($this->filePath);
        }

        $rejected = count($import->rejectedRows);
        $updatedRows = $import->updatedRows ?? [];

        // One summary line; the detail is in the files behind the buttons.
        $counts = $import instanceof DemandManagementWaitlistImport
            ? [
                'nuevas'       => $import->createdRows,
                'actualizadas' => count($updatedRows),
                'sin cambios'  => $import->unchangedRows,
            ]
            : ['importadas' => $import->successfulRows];
        $summary = collect($counts + ['rechazadas' => $rejected])
            ->filter()
            ->map(fn ($count, $label) => number_format($count) . " $label")
            ->implode(' · ');

        // notifyNow: sendToDatabase() would queue one more job for the bell.
        $user->notifyNow(Notification::make()
            ->title($this->title . ': importación finalizada')
            ->body(($summary ?: 'El archivo no tenía filas.') . ($import->importedSheet ? " (hoja «{$import->importedSheet}»)" : ''))
            ->{$rejected ? 'warning' : 'success'}()
            ->actions(array_filter([
                $updatedRows ? NotificationAction::make('cambios')
                    ->label('Descargar cambios')
                    ->button()
                    ->color('gray')
                    ->url(ImportReportExport::changes($updatedRows, $this->filenamePrefix)) : null,
                $rejected ? NotificationAction::make('rechazados')
                    ->label('Descargar rechazados')
                    ->button()
                    ->color('danger')
                    ->url(ImportReportExport::rejected($import->headings, $import->rejectedRows, $this->filenamePrefix)) : null,
            ]))
            ->toDatabase());

        // Workers are long-lived; the next job must not run as this user.
        Auth::forgetUser();
    }

    public function failed(\Throwable $e): void
    {
        Storage::disk(SigteImportService::uploadsDisk())->delete($this->filePath);

        if ($user = User::find($this->uploadedBy)) {
            $user->notifyNow(Notification::make()
                ->title($this->title . ': error durante la importación')
                ->body($e->getMessage())
                ->danger()
                ->toDatabase());
        }
    }
}
