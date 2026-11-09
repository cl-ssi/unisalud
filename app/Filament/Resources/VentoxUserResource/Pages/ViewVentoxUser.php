<?php

declare(strict_types=1);

namespace App\Filament\Resources\VentoxUserResource\Pages;

use App\Filament\Resources\VentoxUserResource;
use Cheesegrits\FilamentGoogleMaps\Infolists\MapEntry;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewVentoxUser extends ViewRecord
{
    protected static string $resource = VentoxUserResource::class;

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
                                            ->columns(2)
                                            ->columnSpan(['sm' => 3, 'xl' => 2])
                                            ->schema([
                                                Components\TextEntry::make('user.text')
                                                    ->label('Nombre Completo')
                                                    ->columnSpan(2)
                                                    ->getStateUsing(fn($record) => $record->user->text ?? "{$record->user->given} {$record->user->fathers_family} {$record->user->mothers_family}"),
                                                Components\TextEntry::make('user.sex')
                                                    ->columnSpan(1)
                                                    ->label('Sexo'),
                                                Components\TextEntry::make('user.gender')
                                                    ->columnSpan(1)
                                                    ->label('Género'),
                                                Components\TextEntry::make('user.birthday')
                                                    ->label('Fecha Nacimiento')
                                                    ->columnSpan(1)
                                                    ->date('Y-m-d'),
                                                Components\TextEntry::make('user.age')
                                                    ->label('Edad')
                                                    ->columnSpan(1),
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
                                    ]),
                            ]),

                        Components\Tabs\Tab::make('Dirección')
                            ->schema([
                                Components\Section::make()
                                    ->columns(['sm' => 3, 'xl' => 6])
                                    ->schema([
                                        Components\Fieldset::make('Ubicación')
                                            ->columns(4)
                                            ->columnSpan('full')
                                            ->schema([
                                                Components\TextEntry::make('user.address.use')
                                                    ->label('Tipo Dirección'),
                                                Components\TextEntry::make('user.address.commune.name')
                                                    ->label('Comuna'),
                                                Components\TextEntry::make('user.address.text')
                                                    ->label('Calle'),
                                                Components\TextEntry::make('user.address.line')
                                                    ->label('Número'),
                                                Components\TextEntry::make('user.address.location.latitude')
                                                    ->label('Latitud'),
                                                Components\TextEntry::make('user.address.location.longitude')
                                                    ->label('Longitud'),
                                                Components\TextEntry::make('risks')
                                                    ->label('Zonas de Riesgo')
                                                    ->badge()
                                                    ->columnSpan(2)
                                                    ->separator(',')
                                                    ->listWithLineBreaks(false)
                                                    ->color(fn(string $state): string => match ($state) {
                                                        'Zona de Inundacion' => 'danger',
                                                        'Zona de Aluvion'    => 'warning',
                                                        default              => 'primary',
                                                    }),
                                            ]),
                                        Components\Section::make('Mapa')
                                            ->columns(4)
                                            ->columnSpan('full')
                                            ->schema([
                                                MapEntry::make('user.address.location.location')
                                                    ->label('')
                                                    ->geoJson(env('APP_URL') . '/json/cota_30_tarapaca.geojson')
                                                    ->columnSpan('full')
                                                    ->defaultZoom(16),
                                            ]),
                                    ]),
                            ]),

                        Components\Tabs\Tab::make('Datos Ventox')
                            ->schema([
                                Components\Section::make()
                                    ->columns(['sm' => 3, 'xl' => 6])
                                    ->schema([
                                        Components\Fieldset::make('Equipo y Terapia')
                                            ->columns(2)
                                            ->columnSpan('full')
                                            ->schema([
                                                Components\TextEntry::make('health_service_check')
                                                    ->label('Servicio Salud Control'),
                                                Components\TextEntry::make('medical_center_control')
                                                    ->label('Hospital Control'),
                                                Components\TextEntry::make('equipment_type')
                                                    ->label('Tipo de equipo'),
                                                Components\TextEntry::make('brand')
                                                    ->label('Marca'),
                                                Components\TextEntry::make('model')
                                                    ->label('Modelo'),
                                                Components\TextEntry::make('therapy_time')
                                                    ->label('Tiempo de terapia')
                                                    ->suffix(' min'),
                                                Components\TextEntry::make('flow')
                                                    ->label('Flujo'),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->activeTab(1)
                    ->columnSpanFull(),
            ]);
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
