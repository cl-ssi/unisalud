<?php

declare(strict_types=1);

namespace App\Filament\Resources\VentoxUserResource\Pages;

use App\Filament\Pages\Concerns\HasHeadingIcon;
use App\Filament\Resources\VentoxUserResource;
use App\Imports\VentoxImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel;
use pxlrbt\FilamentExcel\Columns\Column;

class ListVentoxUsers extends ListRecords
{
    use HasHeadingIcon;

    protected static string $resource = VentoxUserResource::class;

    public function getHeading(): string|Htmlable
    {
        return new HtmlString(Blade::render(
            '<div class="flex items-center">
            <img src="{{asset(\'images/GeoPADDS-Header.png\')}}" alt="GeoPADDS Header" class="img-fluid" style="max-height: 5.5vw;">
            </div>'
        ));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo')
                ->icon('heroicon-o-user-plus'),

            Actions\Action::make('map')
                ->url(fn(\Livewire\Component $livewire) => VentoxUserResource::getUrl(
                    'map',
                    [
                        'search'           => $livewire->getTable()->getFilters()['user']->getForm()->getState()['name'] ?? null,
                        'organizations_id' => $livewire->getTable()->getFilters()['user.mobileContactPoint.organization']->getState()['values'] ?? null,
                        'risks'            => $livewire->getTable()->getFilters()['riesgos']->getState()['values'] ?? null,
                    ]
                ))
                ->icon('heroicon-o-map')
                ->label('Mapa'),

            Actions\Action::make('importar')
                ->label('Importar')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->visible(auth()->user()->can('be god'))
                ->modalHeading('Importar Pacientes Ventox')
                ->modalDescription('Sube el archivo .xlsx para procesar los registros.')
                ->modalSubmitActionLabel('Iniciar Importación')
                ->form([
                    Forms\Components\FileUpload::make('archivo')
                        ->label('Archivo Excel')
                        ->disk('local')
                        ->directory('temp-imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $filePath = Storage::disk('local')->path($data['archivo']);

                        Excel::import(new VentoxImport, $filePath);

                        Storage::disk('local')->delete($data['archivo']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error durante la importación')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            FilamentExcel\Actions\Pages\ExportAction::make()
                ->label('Exportar')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(auth()->user()->hasRole('geoventox_user') || auth()->user()->hasRole('geoventox_admin') || auth()->user()->can('be god'))
                ->exports([
                    FilamentExcel\Exports\ExcelExport::make()
                        ->withFilename(fn($resource) => $resource::getModelLabel() . '-' . date('Y-m-d_H-s'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                        ->fromTable()
                        ->except([
                            'user.officialIdentifier.rut',
                            'risks',
                            'badges',
                            'user.address.full_address',
                            'controls',
                        ])
                        ->withColumns([
                            // Establecimiento
                            Column::make('user.mobileContactPoint.organization.alias')->heading('Establecimiento'),

                            // Paciente
                            Column::make('user.text')->heading('Nombre_Completo'),
                            Column::make('user.given')->heading('Nombre'),
                            Column::make('user.fathers_family')->heading('Apellido_Paterno'),
                            Column::make('user.mothers_family')->heading('Apellido_Materno'),
                            Column::make('user.officialIdentifier.value')->heading('RUN'),
                            Column::make('user.officialIdentifier.dv')->heading('DV'),
                            Column::make('user.sex')->heading('Sexo'),
                            Column::make('user.gender')->heading('Genero'),
                            Column::make('user.birthday')->heading('Fecha_Nacimiento'),
                            Column::make('user.age')->heading('Edad'),
                            Column::make('user.nationality.name')->heading('Nacionalidad'),

                            // Dirección
                            Column::make('user.address.text')->heading('Calle'),
                            Column::make('user.address.line')->heading('Numero'),
                            Column::make('user.address.apartment')->heading('Departamento'),
                            Column::make('user.address.commune.name')->heading('Comuna'),
                            Column::make('user.mobileContactPoint.value')->heading('Telefono'),
                            Column::make('user.address.location.longitude')->heading('Longitud'),
                            Column::make('user.address.location.latitude')->heading('Latitud'),

                            // Datos Ventox
                            Column::make('health_service_check')->heading('Servicio_Salud_Control'),
                            Column::make('medical_center_control')->heading('Hospital_Control'),
                            Column::make('equipment_type')->heading('Tipo_Equipo'),
                            Column::make('brand')->heading('Marca'),
                            Column::make('model')->heading('Modelo'),
                            Column::make('therapy_time')->heading('Tiempo_Terapia'),
                            Column::make('flow')->heading('Flujo'),
                        ]),
                ]),
        ];
    }

    public function getTabs(): array
    {
        return [];
    }
}
