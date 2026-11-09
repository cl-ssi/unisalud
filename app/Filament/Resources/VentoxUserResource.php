<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ventox\Flow;
use App\Enums\ventox\TherapyTime;
use App\Enums\Gender;
use App\Enums\Sex;
use App\Filament\Resources\VentoxUserResource\Pages;
use App\Models\Organization;
use App\Models\VentoxUser;
use App\Services\GeocodingService;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\HtmlString;

class VentoxUserResource extends Resource
{
    protected static ?string $model = VentoxUser::class;

    protected static ?string $slug = 'geoventox';

    protected static ?string $navigationIcon = 'icon-geo-padds';

    protected static ?string $modelLabel = 'Paciente Ventox';

    protected static ?string $pluralModelLabel = 'Pacientes Ventox';

    protected static ?string $navigationLabel = 'GEOVENTOX';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Usuario Dependiente')
                    ->relationship('User')
                    ->schema([
                        Forms\Components\TextInput::make('active')
                            ->hidden()
                            ->default(1),
                        Forms\Components\TextInput::make('text')
                            ->label('Nombre'),
                        Forms\Components\DatePicker::make('birthday')
                            ->label('Fecha de Nacimiento'),
                        Forms\Components\Group::make()
                            ->relationship('officialIdentifier')
                            ->schema([
                                Forms\Components\TextInput::make('run')
                                    ->label('RUN'),
                            ]),
                        Forms\Components\Select::make('sex')
                            ->required()
                            ->label('Sexo')
                            ->options(Sex::class),
                        Forms\Components\Select::make('gender')
                            ->label('Género')
                            ->options(Gender::class),
                    ]),
                Forms\Components\Fieldset::make('Direccion')
                    ->relationship('User')
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(3)
                            ->columnSpan('full')
                            ->relationship('Address')
                            ->schema([
                                Forms\Components\TextInput::make('text')
                                    ->label('Calle'),
                                Forms\Components\TextInput::make('line')
                                    ->label('Número'),
                                Forms\Components\Select::make('commune')
                                    ->relationship(titleAttribute: 'name')
                                    ->label('Ciudad'),
                                Forms\Components\Group::make()
                                    ->columnSpan('full')
                                    ->relationship('location')
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('georreferenciar')
                                                ->label('Buscar Coordenadas')
                                                ->icon('heroicon-m-map-pin')
                                                ->color('primary')
                                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                                    $calle = $get('../text');
                                                    $numero = $get('../line');
                                                    $communeId = $get('../commune');

                                                    if (!$calle || !$numero || !$communeId) {
                                                        Notification::make()
                                                            ->title('Datos incompletos')
                                                            ->body('Debes ingresar calle, número y seleccionar la ciudad.')
                                                            ->warning()
                                                            ->send();

                                                        return;
                                                    }

                                                    try {
                                                        $nombreComuna = \App\Models\Commune::find($communeId)?->name;

                                                        if (!$nombreComuna) {
                                                            throw new \Exception('Comuna no encontrada');
                                                        }
                                                        $geocodingService = app(GeocodingService::class);
                                                        $queryString = $calle . '+' . $numero . '+' . $nombreComuna;
                                                        $coordinates = $geocodingService->getCoordinates($queryString);
                                                        if (!empty($coordinates['lat']) && !empty($coordinates['lng'])) {
                                                            $set('location', [
                                                                'lat' => (float) $coordinates['lat'],
                                                                'lng' => (float) $coordinates['lng'],
                                                            ]);

                                                            Notification::make()
                                                                ->title('Ubicación encontrada')
                                                                ->success()
                                                                ->send();
                                                        } else {
                                                            Notification::make()
                                                                ->title('Sin resultados')
                                                                ->body('No se pudieron obtener coordenadas para esa dirección.')
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    } catch (\Exception $e) {
                                                        Notification::make()
                                                            ->title('Error')
                                                            ->body('Error al geocodificar: ' . $e->getMessage())
                                                            ->danger()
                                                            ->send();
                                                    }
                                                }),
                                        ])->columnSpanFull()->alignCenter(),
                                        Map::make('location')
                                            ->label('Ubicación Exacta')
                                            ->helperText('Si la ubicación automática no es exacta, arrastra el marcador para corregirla.')
                                            ->columnSpanFull()
                                            ->defaultLocation([-20.216700, -70.14222])
                                            ->defaultZoom(15)
                                            ->clickable(false)
                                            ->mapControls([
                                                'zoomControl' => true,
                                                'mapTypeControl' => true,
                                                'scaleControl' => true,
                                                'streetViewControl' => false,
                                                'rotateControl' => false,
                                                'fullscreenControl' => true,
                                            ]),
                                    ]),
                            ]),
                    ]),
                Forms\Components\Fieldset::make('Datos Ventox')
                    ->schema([
                        Forms\Components\TextInput::make('health_service_check')
                            ->label('Servicio Salud Control')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('medical_center_control')
                            ->label('Hospital Control')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('equipment_type')
                            ->label('Tipo de equipo')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('brand')
                            ->label('Marca')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('model')
                            ->label('Modelo')
                            ->maxLength(255),
                        Forms\Components\Select::make('therapy_time')
                            ->label('Tiempo de terapia')
                            ->native(false)
                            ->options(TherapyTime::class),
                        Forms\Components\Select::make('flow')
                            ->label('Flujo')
                            ->native(false)
                            ->options(Flow::class),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description(new HtmlString('Georreferenciación <br> Pacientes Ventox'))
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                if ($user->hasRole('geoventox_user')) {
                    if ($user->exists('organizations')) {
                        $query->whereHas('user', fn($query) => $query->whereHas('mobileContactPoint', fn($query) => $query->whereIn('organization_id', $user->organizations->pluck('id')->toArray())));
                    } else {
                        $query->whereNull('id');
                    }
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.mobileContactPoint.organization.alias')
                    ->wrap()
                    ->label('Establecimiento'),
                Tables\Columns\TextColumn::make('user.text')
                    ->wrap()
                    ->label('Nombre Completo'),
                Tables\Columns\TextColumn::make('user.given')
                    ->hidden()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('user.fathers_family')
                    ->hidden()
                    ->label('Apellido Paterno'),
                Tables\Columns\TextColumn::make('user.mothers_family')
                    ->hidden()
                    ->label('Apellido Materno'),
                Tables\Columns\TextColumn::make('user.officialIdentifier.rut')
                    ->label('RUT'),
                Tables\Columns\TextColumn::make('user.officialIdentifier.value')
                    ->hidden()
                    ->label('RUN'),
                Tables\Columns\TextColumn::make('user.officialIdentifier.dv')
                    ->hidden()
                    ->label('DV'),
                Tables\Columns\TextColumn::make('user.sex')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Sexo'),
                Tables\Columns\TextColumn::make('user.gender')
                    ->hidden()
                    ->label('Genero'),
                Tables\Columns\TextColumn::make('user.birthday')
                    ->hidden()
                    ->label('Fecha Nacimiento')
                    ->date('Y-m-d'),
                Tables\Columns\TextColumn::make('user.age')
                    ->label('Edad'),
                Tables\Columns\TextColumn::make('user.nationality.name')
                    ->hidden()
                    ->label('Nacionalidad'),
                Tables\Columns\TextColumn::make('user.address.full_address')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dirección'),
                Tables\Columns\TextColumn::make('user.address.text')
                    ->hidden()
                    ->label('Calle'),
                Tables\Columns\TextColumn::make('user.address.line')
                    ->hidden()
                    ->label('Número'),
                Tables\Columns\TextColumn::make('user.address.apartment')
                    ->hidden()
                    ->label('Departamento'),
                Tables\Columns\TextColumn::make('user.address.commune.name')
                    ->hidden()
                    ->label('Comuna'),
                Tables\Columns\TextColumn::make('user.mobileContactPoint.value')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Telefono'),
                Tables\Columns\TextColumn::make('user.address.location.longitude')
                    ->hidden()
                    ->label('Longitud'),
                Tables\Columns\TextColumn::make('user.address.location.latitude')
                    ->hidden()
                    ->label('Latitud'),
                Tables\Columns\TextColumn::make('health_service_check')
                    ->label('Servicio Salud Control')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('medical_center_control')
                    ->label('Hospital Control')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('equipment_type')
                    ->label('Tipo de equipo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('therapy_time')
                    ->label('Tiempo de terapia')
                    ->formatStateUsing(fn(?string $state): ?string => $state === null
                        ? null
                        : TherapyTime::tryFrom($state)?->getLabel() ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('flow')
                    ->label('Flujo')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): ?string => $state === null
                        ? null
                        : Flow::tryFrom($state)?->getLabel() ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('risks')
                    ->label('Zonas de Riesgo')
                    ->badge()
                    ->separator(',')
                    ->color(fn(string $state): string => match ($state) {
                        'Zona de Inundacion' => 'danger',
                        'Zona de Aluvion' => 'warning',
                        default => 'primary',
                    }),
            ])
            ->striped()
            ->paginationPageOptions([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->filters([
                Tables\Filters\Filter::make('user')
                    ->columnSpan(1)
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->statePath('name'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['name'] ?? null,
                                fn(Builder $query, $name): Builder => $query->whereHas('user', fn(Builder $query): Builder => $query->where('text', 'like', '%' . $name . '%')),
                            );
                    }),

                Tables\Filters\SelectFilter::make('riesgos')
                    ->label('Riesgos')
                    ->columnSpan(1)
                    ->options([
                        'Zona de Inundacion' => 'Zona de Inundación',
                        'Zona de Aluvion' => 'Zona de Aluvión',
                    ])
                    ->multiple()
                    ->default(Request::query('risks'))
                    ->query(function ($query, $data) {
                        if (!empty($data['values'])) {
                            $query->whereJsonLength('risks', '>', 0);
                            foreach ($data['values'] as $risk) {
                                $query->whereJsonContains('risks', [$risk]);
                            }
                        }
                    }),

                Tables\Filters\SelectFilter::make('user.mobileContactPoint.organization')
                    ->label('Organizacion')
                    ->multiple()
                    ->columnSpan(1)
                    ->preload()
                    ->default(Request::query('organizations_id'))
                    ->modifyQueryUsing(function ($query, $data) {
                        if (!empty($data['values'])) {
                            $query->whereHas('user', function ($query) use ($data) {
                                $query->whereHas('mobileContactPoint', function ($query) use ($data) {
                                    $query->whereHas('organization', function ($query) use ($data) {
                                        $query->whereIn('id', Arr::flatten($data));
                                    });
                                });
                            });
                        }
                    })
                    ->options(function () {
                        return Organization::whereHas('contactPoint', function ($query) {
                            $query->whereNotNull('id');
                        })->with([
                                    'contactPoint' => function ($query) {
                                        $query->has('user')->whereNotNull('contactPoint.id');
                                    },
                                    'contactPoint.user' => function ($query) {
                                        $query->has('ventoxUser')->whereNotNull('contactPoint.user.id');
                                    }
                                ])->pluck('alias', 'id');
                    }),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->filtersFormWidth(MaxWidth::Large)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('map')
                    ->url(fn(Model $record): string => VentoxUserResource::getUrl('map', [
                        'users_id' => [$record->user?->id],
                    ]))
                    ->icon('heroicon-o-map')
                    ->label('Mapa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('map')
                        ->action(function (\Livewire\Component $livewire, Collection $records) {
                            $ids = $records->pluck('user_id')->toArray();

                            return redirect()->to(
                                VentoxUserResource::getUrl('map', [
                                    'search' => $livewire->getTable()->getFilters()['user']->getForm()->getState()['name'] ?? null,
                                    'organizations_id' => $livewire->getTable()->getFilters()['user.mobileContactPoint.organization']->getState()['values'] ?? null,
                                    'risks' => $livewire->getTable()->getFilters()['riesgos']->getState()['values'] ?? null,
                                    'users_id' => $ids ?? null,
                                ])
                            );
                        })
                        ->label('Mapa'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVentoxUsers::route('/'),
            'map' => Pages\MapVentoxUsers::route('/map'),
            'create' => Pages\CreateVentoxUser::route('/create'),
            'view' => Pages\ViewVentoxUser::route('/{record}'),
            'edit' => Pages\EditVentoxUser::route('/{record}/edit'),
        ];
    }
}
