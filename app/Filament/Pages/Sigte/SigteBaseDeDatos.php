<?php

namespace App\Filament\Pages\Sigte;

use App\Jobs\ImportSigteLeCneJob;
use App\Jobs\ImportSigteLeQxJob;
use App\Models\SigteExternalWaitlistImport;
use App\Models\SigteExternalWaitlistRun;
use App\Models\SigteSurgicalWaitlist;
use App\Services\SigteImportService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SigteBaseDeDatos extends Page
{
    protected static ?string $navigationGroup = 'Ingreso LEQ';
    protected static ?string $navigationLabel = 'Base de Datos';
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.sigte.sigte-base-de-datos';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('SIGTE LE QX: administrador')
            || auth()->user()?->can('be god');
    }

    protected function getViewData(): array
    {
        return [
            'lecneMeta'  => $this->getMeta('lecne'),
            'leqxMeta'   => $this->getMeta('leqx'),
            'lastExport' => $this->getLastExportInfo(),
        ];
    }

    private function getLastExportInfo(): ?array
    {
        $lastExportAt = SigteSurgicalWaitlist::max('exported_at');

        if (! $lastExportAt) {
            return null;
        }

        $lastExportRecord = SigteSurgicalWaitlist::with('exportedBy')
            ->where('exported_at', $lastExportAt)
            ->first();

        return [
            'fecha'     => Carbon::parse($lastExportAt)->format('d-m-Y H:i'),
            'usuario'   => $lastExportRecord?->exportedBy?->text ?: '-',
            'pacientes' => SigteSurgicalWaitlist::where('exported_at', $lastExportAt)->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cargarLeCne')
                ->label('Cargar LE CNE')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Cargar Base de Datos LE CNE')
                ->modalDescription('Suba un archivo .xlsx en formato SIGTE (columnas: RUN, DV, NOMBRES, PRIMER_APELLIDO, SEGUNDO_APELLIDO, FECHA_NAC, SEXO, VIA_DIRECCION, NOM_CALLE, NUM_DIRECCION, RESTO_DIRECCION, CIUDAD, COND_RURALIDAD, FONO_FIJO, FONO_MOVIL, EMAIL…)')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo LE CNE (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->disk(SigteImportService::uploadsDisk())
                        ->directory('sigte-uploads')
                        ->visibility('private')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Importar')
                ->action(fn (array $data) => $this->dispatchLeCneImport($data['file'])),

            Action::make('cargarLeQx')
                ->label('Cargar LE Quirúrgica')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Cargar Base de Datos LE Quirúrgica')
                ->modalDescription('Suba un archivo .xlsx o .csv (delimitado por ";") en formato SIGTE con el listado actual de la Lista de Espera Quirúrgica, utilizado para detección de duplicados entre especialidades.')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo LE Quirúrgica (.xlsx o .csv)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            'text/plain',
                            'application/csv',
                        ])
                        ->disk(SigteImportService::uploadsDisk())
                        ->directory('sigte-uploads')
                        ->visibility('private')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Importar')
                ->action(fn (array $data) => $this->dispatchLeQxImport($data['file'])),

            Action::make('descargarDuplicadosLeCne')
                ->label('Duplicados LE CNE')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => $this->hasDuplicates('lecne'))
                ->action(fn () => $this->downloadDuplicates('lecne')),

            Action::make('descargarDuplicadosLeQx')
                ->label('Duplicados LE Quirúrgica')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => $this->hasDuplicates('leqx'))
                ->action(fn () => $this->downloadDuplicates('leqx')),
        ];
    }

    /**
     * Whether $key's most recent import flagged any duplicate RUNs — either
     * repeated within the uploaded file or already present before the
     * upload — worth surfacing a download button for.
     */
    private function hasDuplicates(string $key): bool
    {
        $meta = $this->getMeta($key);

        return (($meta['duplicados_archivo'] ?? 0) + ($meta['duplicados_previos'] ?? 0)) > 0;
    }

    private function downloadDuplicates(string $key): StreamedResponse
    {
        $service = app(SigteImportService::class);

        return Storage::disk('local')->download(
            $service->duplicatesPath($key),
            "SIGTE_{$key}_duplicados.xlsx"
        );
    }

    private function dispatchLeCneImport(string $filePath): void
    {
        app(SigteImportService::class)->markProcessing('lecne', basename($filePath));

        ImportSigteLeCneJob::dispatch($filePath, auth()->id());

        Notification::make()
            ->title('Archivo recibido')
            ->body('Se está procesando en segundo plano — esta página se actualizará sola cuando termine.')
            ->success()
            ->send();
    }

    private function dispatchLeQxImport(string $filePath): void
    {
        app(SigteImportService::class)->markProcessing('leqx', basename($filePath));

        ImportSigteLeQxJob::dispatch($filePath, auth()->id());

        Notification::make()
            ->title('Archivo recibido')
            ->body('Se está procesando en segundo plano — esta página se actualizará sola cuando termine.')
            ->success()
            ->send();
    }

    private function getMeta(string $key): array
    {
        return app(SigteImportService::class)->readMeta($key);
    }

    /**
     * Whether $run appears in the RUN list from the most recently uploaded
     * "LE Quirúrgica" file (another specialty's surgical waitlist export),
     * used to flag possible cross-specialty duplicates during patient search.
     */
    public static function isRunInLeqxList(string $run): bool
    {
        return SigteExternalWaitlistRun::where('run', $run)->exists();
    }

    /**
     * Full row (all spreadsheet columns) for $run from the most recently
     * uploaded "LE Quirúrgica" file, used to pre-fill the entry form when a
     * cross-specialty duplicate is found.
     */
    public static function getLeqxRowData(string $run): ?array
    {
        return SigteExternalWaitlistRun::where('run', $run)->value('data');
    }

    public static function getLeqxUploadMeta(): array
    {
        $import = SigteExternalWaitlistImport::latest()->first();

        if (! $import) {
            return [];
        }

        return [
            'total'   => $import->total_count,
            'fecha'   => $import->created_at->format('d-m-Y H:i'),
            'archivo' => $import->filename,
        ];
    }
}
