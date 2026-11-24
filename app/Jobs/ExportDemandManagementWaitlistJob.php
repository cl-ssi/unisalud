<?php

namespace App\Jobs;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Exports\DemandManagementWaitlistExport;
use App\Exports\ImportReportExport;
use App\Models\User;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

/**
 * Gestión Demanda "Exportar" in the background: the whole list (~35,000
 * rows) takes longer than a web request may run in production. Writes the
 * table's filtered/sorted rows with DemandManagementWaitlistExport and sends
 * the user a database notification with the download button (the file is
 * kept a day, like the import result files).
 */
class ExportDemandManagementWaitlistJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /** ~1.5 s per 1,000 rows; well within a Cloud Tasks request. */
    public int $timeout = 1200;

    public int $tries = 1;

    /**
     * @param string $query the table query, serialized with EloquentSerialize
     */
    public function __construct(
        public string $query,
        public int $requestedBy,
    ) {
    }

    public static function start(Builder $query, int $requestedBy): void
    {
        // Eager loads are added by the export itself.
        self::dispatch(EloquentSerializeFacade::serialize($query->clone()), $requestedBy);
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->requestedBy);
        // The file is stored and served per user.
        Auth::setUser($user);

        try {
            $query = EloquentSerializeFacade::unserialize($this->query);
            $total = $query->clone()->count();
            $url = ImportReportExport::storeFile(
                DemandManagementWaitlistExport::write($query),
                DemandManagementWaitlistExport::filename(),
            );

            // notifyNow: sendToDatabase() would queue one more job for the bell.
            $user->notifyNow(Notification::make()
                ->title('Gestión Demanda: exportación lista')
                ->body(number_format($total, 0, ',', '.') . ' filas. El enlace es válido por 24 horas.')
                ->success()
                ->actions([
                    NotificationAction::make('descargar')
                        ->label('Descargar')
                        ->button()
                        ->url($url),
                ])
                ->toDatabase());
        } finally {
            // Workers are long-lived; the next job must not run as this user.
            Auth::forgetUser();
        }
    }

    public function failed(\Throwable $e): void
    {
        if ($user = User::find($this->requestedBy)) {
            $user->notifyNow(Notification::make()
                ->title('Gestión Demanda: error durante la exportación')
                ->body($e->getMessage())
                ->danger()
                ->toDatabase());
        }
    }
}
