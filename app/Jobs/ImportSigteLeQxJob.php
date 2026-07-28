<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SigteImportService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportSigteLeQxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(public string $filePath, public ?int $uploadedBy)
    {
    }

    public function handle(SigteImportService $importer): void
    {
        try {
            $stats = $importer->processLeQx($this->filePath, $this->uploadedBy);

            $this->notifyUploader(fn () => ($stats['empty'] ?? false)
                ? Notification::make()->warning()
                    ->title('Archivo LE Quirúrgica vacío')
                    ->body('El archivo subido no contenía filas para procesar.')
                : Notification::make()->success()
                    ->title('Carga LE Quirúrgica completada')
                    ->body("{$stats['total']} registros cargados"));
        } finally {
            Storage::disk(SigteImportService::uploadsDisk())->delete($this->filePath);
        }
    }

    public function failed(\Throwable $e): void
    {
        // processLeQx wraps the delete-and-replace in a DB transaction, so a
        // failure rolls back cleanly and the previous snapshot stays intact;
        // only the "estado" flips to error so the page can show it.
        app(SigteImportService::class)->markFailed('leqx', $e->getMessage());

        $this->notifyUploader(fn () => Notification::make()->danger()
            ->title('Error al procesar LE Quirúrgica')
            ->body($e->getMessage()));

        Log::error('LE Quirúrgica import failed', ['file' => $this->filePath, 'exception' => $e]);

        Storage::disk(SigteImportService::uploadsDisk())->delete($this->filePath);
    }

    private function notifyUploader(\Closure $makeNotification): void
    {
        $user = $this->uploadedBy ? User::find($this->uploadedBy) : null;

        if ($user) {
            $makeNotification()->sendToDatabase($user);
        }
    }
}
