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

class ImportSigteLeCneJob implements ShouldQueue
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
            $stats = $importer->processLeCne($this->filePath);

            $this->notifyUploader(function () use ($stats) {
                if ($stats['empty'] ?? false) {
                    return Notification::make()->warning()
                        ->title('Archivo LE CNE vacío')
                        ->body('El archivo subido no contenía filas para procesar.');
                }

                $errores = $stats['errores'] ?? 0;

                return Notification::make()
                    ->status($errores > 0 ? 'warning' : 'success')
                    ->title('Carga LE CNE completada')
                    ->body(
                        "{$stats['nuevos']} nuevos · {$stats['actualizados']} actualizados"
                        . ($errores > 0 ? " · {$errores} filas con error" : '')
                    );
            });
        } finally {
            Storage::disk('local')->delete($this->filePath);
        }
    }

    public function failed(\Throwable $e): void
    {
        // Keeps the last successful import's totals in the meta — only the
        // "estado" flips to error — so the page still shows what's actually
        // loaded while flagging that the latest attempt didn't finish.
        app(SigteImportService::class)->markFailed('lecne', $e->getMessage());

        $this->notifyUploader(fn () => Notification::make()->danger()
            ->title('Error al procesar LE CNE')
            ->body($e->getMessage()));

        Log::error('LE CNE import failed', ['file' => $this->filePath, 'exception' => $e]);

        Storage::disk('local')->delete($this->filePath);
    }

    private function notifyUploader(\Closure $makeNotification): void
    {
        $user = $this->uploadedBy ? User::find($this->uploadedBy) : null;

        if ($user) {
            $makeNotification()->sendToDatabase($user);
        }
    }
}
