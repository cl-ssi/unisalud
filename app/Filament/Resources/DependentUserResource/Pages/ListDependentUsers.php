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

    protected static string $resource = DependentUserResource::class;

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
                ->url(fn(\Livewire\Component $livewire) => route(
                    'filament.admin.resources.dependent-users.map',
                    [
                        'conditions_multiple' => $livewire->getTableFilterState('conditions_multiple') ?? null, // INFO: NEW BEST WAY 
                        // 'conditions_id' => $livewire->getTable()->getFilters()['conditions-multiple']['conditions']->getState('name')['values'] ?? null,
                        'search' => $livewire->getTable()->getFilters()['user']->getForm()->getState()['name'] ?? null,
                        'organizations_id' => $livewire->getTable()->getFilters()['user.mobileContactPoint.organization']->getState()['values'] ?? null,
                        'risks' => $livewire->getTable()->getFilters()['riesgos']->getState()['values'] ?? null,
                    ]
                ))
                ->icon('heroicon-o-map')
                ->label('Mapa'),
            Import::make()
                ->import(DependentUserImport::class)
                ->type(\Maatwebsite\Excel\Excel::XLSX)
                ->label('Importar')
                // ->visible(auth()->user()->can('be god'))
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
                            'controls',
                            'dependentCaregiver.controls'
                        ])
                        ->withColumns([
                            // Datos del establecimiento
                            Column::make('user.mobileContactPoint.organization.alias')->heading('Establecimiento'),

                            // Datos del paciente
                            Column::make('user.text')->heading('Nombre_Completo'),
                            Column::make('user.given')->heading('Nombre'),
                            Column::make('user.fathers_family')->heading('Apellido_Paterno'),
                            Column::make('user.mothers_family')->heading('Apellido_Materno'),
                            Column::make('user.officialIdentifier.value')->heading('RUN'),
                            Column::make('user.officialIdentifier.dv')->heading('DV'),
                            Column::make('healthcare_type')->heading('Prevision'),
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

                            // Diagnóstico y condiciones
                            Column::make('diagnosis')->heading('Diagnostico'),
                            Column::make('conditions.name')->heading('Condiciones'),

                            // Fechas y visitas
                            Column::make('check_in_date')->heading('Fecha_Ingreso'),
                            Column::make('check_out_date')->heading('Fecha_Egreso'),
                            Column::make('integral_visits')->heading('Visitas_Integrales'),
                            Column::make('last_integral_visit')->heading('Ultima_Visita_Integral'),
                            Column::make('treatment_visits')->heading('Visitas_Tratamiento'),
                            Column::make('last_treatment_visit')->heading('Ultima_Visita_Tratamiento'),

                            // Evaluaciones y controles
                            Column::make('barthel')->heading('Barthel'),
                            Column::make('empam')->heading('EMPAM'),
                            Column::make('eleam')->heading('ELEAM'),
                            Column::make('upp')->heading('UPP'),
                            Column::make('elaborated_plan')->heading('Plan_Elaborado'),
                            Column::make('evaluated_plan')->heading('Plan_Evaluado'),

                            // Vacunas
                            Column::make('pneumonia')->heading('Neumonia'),
                            Column::make('influenza')->heading('Influenza'),
                            Column::make('covid_19')->heading('Covid19'),

                            // Ayudas técnicas
                            Column::make('tech_aid')->heading('Ayuda_Tecnica'),
                            Column::make('tech_aid_date')->heading('Fecha_Ayuda_Tecnica'),
                            Column::make('nutrition_assistance')->heading('Entrega_Alimentacion'),
                            Column::make('nutrition_assistance_date')->heading('Fecha_Entrega_Alimentacion'),
                            Column::make('diapers_size')->heading('Talla_Panal'),
                            Column::make('nasogastric_catheter')->heading('Sonda_Nasogastrica'),
                            Column::make('urinary_catheter')->heading('Sonda_Urinaria'),
                            Column::make('extra_info')->heading('Informacion_Extra'),

                            // Datos del cuidador
                            Column::make('dependentCaregiver.relative')->heading('Parentesco_Cuidador'),
                            Column::make('dependentCaregiver.user.text')->heading('Nombre_Completo_Cuidador'),
                            Column::make('dependentCaregiver.user.given')->heading('Nombre_Cuidador'),
                            Column::make('dependentCaregiver.user.fathers_family')->heading('Apellido_Paterno_Cuidador'),
                            Column::make('dependentCaregiver.user.mothers_family')->heading('Apellido_Materno_Cuidador'),
                            Column::make('dependentCaregiver.user.officialIdentifier.value')->heading('RUN_Cuidador'),
                            Column::make('dependentCaregiver.user.officialIdentifier.dv')->heading('DV_Cuidador'),
                            Column::make('dependentCaregiver.healthcare_type')->heading('Prevision_Cuidador'),
                            Column::make('dependentCaregiver.user.sex')->heading('Sexo_Cuidador'),
                            Column::make('dependentCaregiver.user.gender')->heading('Genero_Cuidador'),
                            Column::make('dependentCaregiver.user.birthday')->heading('Fecha_Nacimiento_Cuidador'),
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
