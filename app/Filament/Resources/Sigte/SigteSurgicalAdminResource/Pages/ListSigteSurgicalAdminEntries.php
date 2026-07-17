<?php

namespace App\Filament\Resources\Sigte\SigteSurgicalAdminResource\Pages;

use App\Enums\SurgicalComplexity;
use App\Exports\SigteSurgicalExport;
use App\Filament\Resources\Sigte\SigteSurgicalAdminResource;
use App\Filament\Widgets\SigteSurgicalStatsWidget;
use App\Models\SigteSurgicalExportBatch;
use App\Models\SigteSurgicalProcedureCode;
use App\Models\SigteSurgicalWaitlist;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ListSigteSurgicalAdminEntries extends ListRecords
{
    protected static string $resource = SigteSurgicalAdminResource::class;

    protected static ?string $title = 'Todos los Ingresos';

    protected function getHeaderWidgets(): array
    {
        return [
            SigteSurgicalStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar')
                ->label('Exportar SIGTE')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    DatePicker::make('desde')
                        ->label('Desde (F. Entrada)')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    DatePicker::make('hasta')
                        ->label('Hasta (F. Entrada)')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    Select::make('requesting_professional_id')
                        ->label('Cirujano')
                        ->placeholder('Todos')
                        ->options(fn () => User::whereIn(
                            'id',
                            SigteSurgicalWaitlist::distinct()->pluck('requesting_professional_id')
                        )->get()->mapWithKeys(fn ($u) => [
                            $u->id => $u->text ?: trim("{$u->given} {$u->fathers_family}"),
                        ]))
                        ->searchable(),
                    Select::make('status')
                        ->label('Estado')
                        ->placeholder('Todos')
                        ->options([
                            'completo'   => 'Completo',
                            'incompleto' => 'Incompleto',
                        ]),
                    Select::make('complexity')
                        ->label('Complejidad')
                        ->placeholder('Todas')
                        ->options(
                            collect(SurgicalComplexity::cases())
                                ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
                        ),
                ])
                ->action(function (array $data) {
                    $filters = array_filter([
                        'desde'                       => $data['desde'] ?? null,
                        'hasta'                        => $data['hasta'] ?? null,
                        'requesting_professional_id'  => $data['requesting_professional_id'] ?? null,
                        'status'                       => $data['status'] ?? null,
                        'complexity'                   => $data['complexity'] ?? null,
                    ]);

                    $matchingIds = SigteSurgicalExport::query($filters)->pluck('id');

                    SigteSurgicalWaitlist::whereIn('id', $matchingIds)
                        ->update(['exported_at' => now(), 'exported_by' => auth()->id()]);

                    $batch = SigteSurgicalExportBatch::create([
                        'exported_by'                 => auth()->id(),
                        'desde'                        => $filters['desde'] ?? null,
                        'hasta'                        => $filters['hasta'] ?? null,
                        'requesting_professional_id'  => $filters['requesting_professional_id'] ?? null,
                        'status'                       => $filters['status'] ?? null,
                        'complexity'                   => $filters['complexity'] ?? null,
                        'patients_count'               => $matchingIds->count(),
                    ]);
                    $batch->waitlistEntries()->attach(
                        $matchingIds->mapWithKeys(fn ($id) => [$id => ['created_at' => now()]])
                    );

                    $suffix = (($filters['desde'] ?? null) || ($filters['hasta'] ?? null))
                        ? '_' . ($filters['desde'] ?? 'inicio') . '_' . ($filters['hasta'] ?? 'hoy')
                        : '_' . now()->format('Y-m-d');

                    return Excel::download(
                        new SigteSurgicalExport($matchingIds->all()),
                        'SIGTE_LEQx' . $suffix . '.xlsx'
                    );
                }),

            Action::make('cargarCodigosFonasa')
                ->label('Cargar Códigos FONASA')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Cargar Códigos FONASA')
                ->modalDescription('Suba un archivo .xlsx con columnas: CODIGO, NOMBRE, Complejidad Nueva (además de otras columnas que se ignoran: Base REM, Tipo de Prestación, Código SIGTE, Especialidad, Temporabilidad, Vigencia Entrada, Vigencia Salida). Los códigos existentes (misma complejidad y código) se actualizan; el resto se crea.')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo Códigos FONASA (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->disk('local')
                        ->directory('sigte-uploads')
                        ->visibility('private')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Importar')
                ->action(fn (array $data) => $this->processCodigosFonasa($data['file'])),
        ];
    }

    private function processCodigosFonasa(string $filePath): void
    {
        try {
            $fullPath = Storage::disk('local')->path($filePath);
            $spreadsheet = IOFactory::load($fullPath);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

            if (empty($rows)) {
                Notification::make()->title('El archivo está vacío')->danger()->send();
                return;
            }

            $headers = array_map(fn ($h) => Str::of($h)->trim()->upper()->toString(), array_shift($rows));
            $complexityMap = collect(SurgicalComplexity::cases())->flatMap(fn ($c) => [
                $c->value                                                          => $c->value,
                Str::of($c->getLabel())->lower()->ascii()->replace(' ', '_')->toString() => $c->value,
            ]);

            $nuevos = $actualizados = $errores = 0;

            foreach ($rows as $rowData) {
                if (! array_filter($rowData, fn ($v) => $v !== null && $v !== '')) {
                    continue;
                }
                if (count($rowData) < count($headers)) {
                    continue;
                }

                $data = array_combine($headers, array_map(fn ($v) => trim((string) ($v ?? '')), $rowData));
                $code = $data['CODIGO'] ?? '';
                $text = $data['NOMBRE'] ?? '';
                $complexityRaw = Str::of($data['COMPLEJIDAD NUEVA'] ?? '')->lower()->ascii()->replace(' ', '_')->toString();
                $complexity = $complexityMap[$complexityRaw] ?? null;

                if (! $code || ! $complexity) {
                    $errores++;
                    continue;
                }

                $procedureCode = SigteSurgicalProcedureCode::updateOrCreate(
                    ['complexity' => $complexity, 'code' => $code],
                    ['text' => $text ?: null]
                );

                $procedureCode->wasRecentlyCreated ? $nuevos++ : $actualizados++;
            }

            Storage::disk('local')->delete($filePath);

            $total = $nuevos + $actualizados;
            Notification::make()
                ->title("{$total} códigos FONASA procesados")
                ->body("{$nuevos} nuevos · {$actualizados} actualizados" . ($errores ? " · {$errores} errores" : ''))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al procesar el archivo')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
