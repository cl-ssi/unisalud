<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Schema\View;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewDependentUser extends ViewRecord
{
    protected static string $resource = DependentUserResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            Components\Tabs::make('Información')
                ->tabs([
                    Components\Tabs\Tab::make('Datos Personales')
                        ->schema([
                            Components\Section::make()
                                ->columns(['sm' => 3, 'xl' => 6])
                                ->schema([
                                    Components\Fieldset::make('Información Personal')
                                        ->columns(1)
                                        ->columnSpan(['sm' => 3, 'xl' => 2])
                                        ->schema([
                                            Components\TextEntry::make('user.text')
                                                ->label('Nombre Completo')
                                                ->getStateUsing(fn ($record) => $record->user->text ?? "{$record->user->given} {$record->user->fathers_family} {$record->user->mothers_family}"),
                                            Components\TextEntry::make('user.sex')
                                                ->label('Sexo'),
                                            Components\TextEntry::make('user.gender')
                                                ->label('Género'),
                                            Components\Split::make([
                                                Components\TextEntry::make('user.birthday')
                                                    ->label('Fecha Nacimiento')
                                                    ->date('Y-m-d'),
                                                Components\TextEntry::make('user.age')
                                                    ->label('Edad'),
                                            ]),
                                        ]),
                                    
                                    Components\Fieldset::make('Contacto')
                                        ->columns(1)
                                        ->columnSpan(['sm' => 3, 'xl' => 2])
                                        ->schema([
                                            Components\TextEntry::make('user.mobileContactPoint.organization.alias')
                                                ->label('Establecimiento'),
                                            Components\TextEntry::make('user.mobileContactPoint.value')
                                                ->label('Teléfono'),
                                        ]),
                                    
                                    Components\Fieldset::make('Programa')
                                        ->columns(1)
                                        ->columnSpan(['sm' => 3, 'xl' => 2])
                                        ->schema([
                                            Components\TextEntry::make('diagnosis')
                                                ->label('Diagnóstico'),
                                            Components\TextEntry::make('healthcare_type')
                                                ->label('Previsión'),
                                            Components\Split::make([
                                                Components\TextEntry::make('check_in_date')
                                                    ->label('Ingreso')
                                                    ->date(),
                                                Components\TextEntry::make('check_out_date')
                                                    ->label('Egreso')
                                                    ->date(),
                                            ]),
                                        ]),
                                ]),
                        ]),
                    
                    Components\Tabs\Tab::make('Dirección')
                        ->schema([
                            Components\Section::make()
                                ->columns(['sm' => 3, 'xl' => 6])
                                ->schema([
                                    Components\Fieldset::make('Ubicación')
                                        ->columns(2)
                                        ->columnSpan(['sm' => 3, 'xl' => 3])
                                        ->schema([
                                            Components\TextEntry::make('user.address.use')
                                                ->label('Tipo Dirección'),
                                            Components\TextEntry::make('user.address.commune.name')
                                                ->label('Comuna'),
                                            Components\TextEntry::make('user.address.text')
                                                ->label('Calle')
                                                ->columnSpan(2),
                                            Components\TextEntry::make('user.address.line')
                                                ->label('Número'),
                                        ]),
                                    
                                    Components\Fieldset::make('Coordenadas')
                                        ->columns(2)
                                        ->columnSpan(['sm' => 3, 'xl' => 3])
                                        ->schema([
                                            Components\TextEntry::make('user.address.location.latitude')
                                                ->label('Latitud'),
                                            Components\TextEntry::make('user.address.location.longitude')
                                                ->label('Longitud'),
                                            Components\TextEntry::make('location_map')
                                                // ->view('components.location-map')
                                                ->columnSpan(2),
                                        ]),
                                ]),
                        ]),
                    
                    Components\Tabs\Tab::make('Cuidador')
                        ->schema([
                            Components\Section::make()
                                ->columns(['sm' => 3, 'xl' => 6])
                                ->schema([
                                    Components\Fieldset::make('Información Cuidador')
                                        ->columns(2)
                                        ->columnSpan(['sm' => 3, 'xl' => 3])
                                        ->schema([
                                            Components\TextEntry::make('dependentCaregiver.user.text')
                                                ->label('Nombre'),
                                            Components\TextEntry::make('dependentCaregiver.user.age')
                                                ->label('Edad'),
                                            Components\TextEntry::make('dependentCaregiver.relative')
                                                ->label('Parentesco'),
                                            Components\TextEntry::make('dependentCaregiver.healthcare_type')
                                                ->label('Previsión'),
                                            Components\TextEntry::make('dependentCaregiver.empam')
                                                ->label('EMPAM'),
                                            Components\TextEntry::make('dependentCaregiver.zarit')
                                                ->label('Zarit'),
                                        ]),
                                    
                                    Components\Fieldset::make('Seguimiento Cuidador')
                                        ->columns(2)
                                        ->columnSpan(['sm' => 3, 'xl' => 3])
                                        ->schema([
                                            Components\TextEntry::make('dependentCaregiver.immunizations')
                                                ->label('Inmunizaciones'),
                                            Components\TextEntry::make('dependentCaregiver.elaborated_plan')
                                                ->label('Plan Elaborado'),
                                            Components\TextEntry::make('dependentCaregiver.evaluated_plan')
                                                ->label('Plan Evaluado'),
                                            Components\TextEntry::make('dependentCaregiver.trained')
                                                ->label('Capacitado'),
                                            Components\TextEntry::make('dependentCaregiver.stipend')
                                                ->label('Estipendio'),
                                        ]),
                                ]),
                        ]),
                    
                    Components\Tabs\Tab::make('Visitas')
                        ->schema([
                            Components\Grid::make()
                                ->columns(['sm' => 2, 'xl' => 4])
                                ->schema([
                                    Components\Fieldset::make('Visitas Integrales')
                                        ->schema([
                                            Components\TextEntry::make('integral_visits')
                                                ->label('Cantidad')
                                                ->numeric(),
                                            Components\TextEntry::make('last_integral_visit')
                                                ->label('Última Visita')
                                                ->date(),
                                        ]),
                                    
                                    Components\Fieldset::make('Visitas de Tratamiento')
                                        ->schema([
                                            Components\TextEntry::make('treatment_visits')
                                                ->label('Cantidad')
                                                ->numeric(),
                                            Components\TextEntry::make('last_treatment_visit')
                                                ->label('Última Visita')
                                                ->date(),
                                        ]),
                                    
                                    Components\Fieldset::make('Evaluaciones')
                                        ->schema([
                                            Components\TextEntry::make('barthel')
                                                ->label('Barthel'),
                                        ]),
                                ]),
                        ]),
                ])
                ->activeTab(1)
                ->columnSpanFull(),
        ]);
    }

}
