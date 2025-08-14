<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

use YOS\FilamentExcel\Actions\Import;
use App\Imports\DependentUserImport;
use pxlrbt\FilamentExcel;
use pxlrbt\FilamentExcel\Columns\Column;

use App\Filament\Pages\Concerns\HasHeadingIcon;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

class ListDependentUsers extends ListRecords
{
    use HasHeadingIcon;

    public function getHeading(): string|Htmlable
    {
        /* 
        return new HtmlString($this->getHeadingWithIcon(
            heading: 'Dependientes severos',
            icon: 'icon-dependent-temp',
            iconPosition: IconPosition::Before,
            iconSize: IconSize::Large
        )); 
        */

        return new HtmlString(Blade::render('<div class="flex items-center">
                <x-icon-dependent-temp 
                style="--c-600: var(--black-600);margin-inline-end: .5rem; width: 4rem; height: 5rem;"
                class="inline text-custom-600"
                /> Dependientes Severos 
            </div>'));
    }

    protected static string $resource = DependentUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo')
                ->icon('heroicon-o-user-plus'),
            Actions\Action::make('map')
                ->url(fn(\Livewire\Component $livewire) => route(
                    'filament.admin.resources.dependent-users.map',
                    [
                        'conditions_id' => $livewire->getTable()->getFilters()['conditions']->getState('name')['values'] ?? null,
                        'search' => $livewire->getTable()->getFilters()['user']->getForm()->getState()['name'] ?? null,
                        'organizations_id' => $livewire->getTable()->getFilters()['user.mobileContactPoint.organization']->getState()['values'] ?? null,
                    ]
                ))
                ->icon('heroicon-o-map')
                ->label('Mapa'),
            Import::make()
                ->import(DependentUserImport::class)
                ->type(\Maatwebsite\Excel\Excel::XLSX)
                ->label('Importar')
                ->visible(auth()->user()->can('be god'))
                ->hint('Subir archivo de tipo xlsx')
                ->icon('heroicon-o-arrow-down-tray'),
            FilamentExcel\Actions\Pages\ExportAction::make()
                ->label('Exportar')
                ->icon('heroicon-o-arrow-up-tray')
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
                            'dependentCaregiver.controls'
                        ])
                        ->withColumns([
                            Column::make('dependentCaregiver.relative')->heading('Parentesco_Cuidador'),
                            Column::make('dependentCaregiver.user.given')->heading('Nombre_Cuidador'),
                            Column::make('dependentCaregiver.user.fathers_family')->heading('Apellido_Paterno_Cuidador'),
                            Column::make('dependentCaregiver.user.mothers_family')->heading('Apellido_Materno_Cuidador'),
                            Column::make('dependentCaregiver.user.age')->heading('Edad_Cuidador'),
                            Column::make('dependentCaregiver.healthcare_type')->heading('Prevision_Cuidador'),
                            Column::make('dependentCaregiver.empam')->heading('Empam_Cuidador'),
                            Column::make('dependentCaregiver.zarit')->heading('Zarit_Cuidador'),
                            Column::make('dependentCaregiver.immunizations')->heading('Inmunizaciones_Cuidador'),
                            Column::make('dependentCaregiver.elaborated_plan')->heading('Plan_Elaborado_Cuidador'),
                            Column::make('dependentCaregiver.evaluated_plan')->heading('Plan_Evaluado_Cuidador'),
                            Column::make('dependentCaregiver.trained')->heading('Capacitacion_Cuidador'),
                            Column::make('dependentCaregiver.stipend')->heading('Estipendio_Cuidador'),
                        ]),
                ]),
        ];
    }

    public function getTabs(): array
    {
        return [];
    }
}
