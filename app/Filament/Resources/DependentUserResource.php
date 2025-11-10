<?php

namespace App\Filament\Resources;


use App\Models\Condition;
use App\Models\Organization;
use App\Models\DependentUser;
use App\Models\DependentCaregiver;

use App\Filament\Resources\DependentUserResource\Pages;
use App\Filament\Resources\DependentUserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\MaxWidth;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;



class DependentUserResource extends Resource
{
    protected static ?string $model = DependentUser::class;

    protected static ?string $navigationIcon = 'icon-geo-padds';

    protected static ?string $modelLabel = 'Dependiente severo';

    protected static ?string $pluralModelLabel = 'Dependientes severos';

    protected static ?string $navigationLabel = 'GEO PADDS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Usuario Dependiente')
                    ->relationship('User')
                    ->schema([
                        Forms\Components\TextInput::make('text')
                            ->label('Nombre')
                            ->disabled(),
                        Forms\Components\DatePicker::make('birthday')
                            ->date('d/m/Y')
                            ->label('Fecha de Nacimiento')
                            ->disabled(),
                        Forms\Components\Group::make()
                            ->relationship('officialIdentifier')
                            ->schema([
                                Forms\Components\TextInput::make('run')
                                    ->label('RUN')
                                    ->disabled(),
                            ]),
                        Forms\Components\TextInput::make('sex')
                            ->label('Sexo')
                            ->disabled(),
                        Forms\Components\TextInput::make('gender')
                            ->label('Genero')
                            ->disabled(),
                    ]),
                Forms\Components\Fieldset::make('Cuidador')
                    ->relationship('DependentCaregiver')
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(2)
                            ->columnSpan('full')
                            ->relationship('User')
                            ->schema([
                                Forms\Components\TextInput::make('text')
                                    ->label('Nombre')
                                    ->disabled(),
                                Forms\Components\DatePicker::make('birthday')
                                    ->label('Fecha de Nacimiento')
                                    ->disabled(),
                                Forms\Components\Group::make()
                                    ->relationship('officialIdentifier')
                                    ->schema([
                                        Forms\Components\TextInput::make('run')
                                            ->label('RUN')
                                            ->disabled(),
                                    ]),
                                Forms\Components\TextInput::make('sex')
                                    ->label('Sexo')
                                    ->disabled(),
                                Forms\Components\TextInput::make('gender')
                                    ->label('Genero')
                                    ->disabled(),
                            ]),
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
                            ]),
                    ]),
                Forms\Components\Toggle::make('flood_zone')
                    ->label('Zona de Inundabilidad'),
                Forms\Components\Textarea::make('diagnosis')
                    ->label('Diagnostico')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('check_in_date')
                    ->label('Fecha de Ingreso'),
                Forms\Components\DatePicker::make('check_out_date')
                    ->label('Fecha de Egreso'),
                Forms\Components\TextInput::make('integral_visits')
                    ->label('Vistas Integrales')
                    ->numeric()
                    ->extraAttributes(fn(Model $record) => ($record->integral_visits == null) ? ['class' => 'bg-danger-300 dark:bg-danger-600'] : []),
                Forms\Components\DatePicker::make('last_integral_visit')
                    ->label('Última Visita Integral'),
                Forms\Components\TextInput::make('treatment_visits')
                    ->label('Visitas de Tratamiento')
                    ->numeric(),
                Forms\Components\DatePicker::make('last_treatment_visit')
                    ->label('Última Visita de Tratamiento'),
                Forms\Components\Select::make('barthel')
                    ->options([
                        'independent' => 'Independiente',
                        'slight' => 'Leve',
                        'moderate' => 'Moderado',
                        'severe' => 'Grave',
                        'total' => 'Total',
                    ]),
                Forms\Components\Toggle::make('empam'),
                Forms\Components\Toggle::make('eleam'),
                Forms\Components\Toggle::make('upp'),
                Forms\Components\Toggle::make('elaborated_plan'),
                Forms\Components\Toggle::make('evaluated_plan'),
                Forms\Components\DatePicker::make('pneumonia')
                    ->label('Neumonia'),
                Forms\Components\DatePicker::make('influenza'),
                Forms\Components\DatePicker::make('covid_19'),
                Forms\Components\Textarea::make('extra_info')
                    ->label('Informacion Adicional')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('tech_aid')
                    ->label('Ayuda Técnica'),
                Forms\Components\DatePicker::make('tech_aid_date')
                    ->label('Fecha Ayuda Técnica'),
                Forms\Components\Toggle::make('nutrition_assistance')
                    ->label('Entrega de Alimentación'),
                Forms\Components\DatePicker::make('nutrition_assistance_date')
                    ->label('Fecha Entrega de Alimentación'),
                Forms\Components\Select::make('nasogastric_catheter')
                    ->label('Sonda Nasogástrica')
                    ->options([
                        null => 'No Aplica',
                        '10' => '10',
                        '12' => '12',
                        '14' => '14',
                        '16' => '16',
                        '18' => '18',
                        '20' => '20',
                    ]),
                Forms\Components\Select::make('urinary_catheter')
                    ->label('Sonda Nasogástrica')
                    ->options([
                        null => 'No Aplica',
                        '12' => '12',
                        '14' => '14',
                        '16' => '16',
                        '18' => '18',
                        '20' => '20',
                        '22' => '22',
                        '24' => '24',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description(new HtmlString('Georreferenciación <br>
             Programa de Atención Domiciliaria para personas con Dependencia Severa y Cuidadores'))
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                if ($user->hasRole('geopadds_user')) {
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
                Tables\Columns\TextColumn::make('healthcare_type')
                    ->hidden()
                    ->label('Prevision'),
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
                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnostico')
                    // ->listWithLineBreaks()
                    ->bulleted()
                    ->separator('/')
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->searchable(),
                Tables\Columns\TextColumn::make('conditions.name')
                    ->label('Condiciones')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => Str::ucwords($state))
                    ->color(fn(string $state): string => match (true) {
                        Str::contains($state, 'electrodependencia')          => 'fuchsia',
                        Str::contains($state, 'movilidad reducida')            => 'amber',
                        Str::contains($state, 'oxigeno dependient')            => 'sky',
                        Str::contains($state, 'alimentacion enteral')              => 'violet',
                        Str::contains($state, 'oncologicos')  => 'lime',
                        Str::contains($state, 'cuidados paliativos universales')   => 'teal',
                        Str::contains($state, 'naneas')        => 'orange',
                        Str::contains($state, 'asistencia ventilatoria no invasiva')        => 'stone',
                        Str::contains($state, 'asistencia ventilatoria invasiva')         => 'slate',
                        Str::contains($state, 'concentradores de oxigeno')         => 'neutral',
                        default                                                           => 'primary',
                    }),
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
                Tables\Columns\TextColumn::make('check_in_date')
                    ->label('Fecha de Ingreso')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_date')
                    ->label('Fecha de Egreso')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('integral_visits')
                    ->label('Vistas Integrales')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_integral_visit')
                    ->label('Última Visita Integral')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('treatment_visits')
                    ->label('Visitas de Tratamiento')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_treatment_visit')
                    ->label('Última Visita de Tratamiento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('risks')
                    ->label('Zonas de Riesgo')
                    ->badge()
                    ->separator(',')
                    ->color(fn(string $state): string => match ($state) {
                        'Zona de Inundacion'    => 'danger',
                        'Zona de Aluvion'       => 'warning',
                        default                 => 'primary',
                    }),
                Tables\Columns\TextColumn::make('controls')
                    ->label('Controles')
                    ->badge()
                    ->separator(',')
                    ->color(fn(string $state): string => match (true) {
                        Str::contains($state, DependentUser::getLabel('barthel'))          => 'fuchsia',
                        Str::contains($state, DependentUser::getLabel('empam'))            => 'amber',
                        Str::contains($state, DependentUser::getLabel('eleam'))            => 'sky',
                        Str::contains($state, DependentUser::getLabel('upp'))              => 'violet',
                        Str::contains($state, DependentUser::getLabel('elaborated_plan'))  => 'lime',
                        Str::contains($state, DependentUser::getLabel('evaluated_plan'))   => 'teal',
                        Str::contains($state, DependentUser::getLabel('pneumonia'))        => 'orange',
                        Str::contains($state, DependentUser::getLabel('influenza'))        => 'stone',
                        Str::contains($state, DependentUser::getLabel('covid-19'))         => 'slate',
                        default                                                           => 'primary',
                    }),
                Tables\Columns\TextColumn::make('barthel')
                    ->hidden()
                    ->label('Barthel'),
                Tables\Columns\IconColumn::make('empam')
                    ->hidden()
                    ->label('Emp / Empam')
                    ->boolean(),
                Tables\Columns\IconColumn::make('eleam')
                    ->hidden()
                    ->label('Eleam')
                    ->boolean(),
                Tables\Columns\IconColumn::make('upp')
                    ->hidden()
                    ->label('UPP')
                    ->boolean(),
                Tables\Columns\IconColumn::make('elaborated_plan')
                    ->hidden()
                    ->label('Plan Elaborado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('evaluated_plan')
                    ->hidden()
                    ->label('Plan Evaluado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('pneumonia')
                    ->hidden()
                    ->label('Neumonia')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('influenza')
                    ->hidden()
                    ->label('Influenza')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('covid_19')
                    ->hidden()
                    ->label('Covid-19')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('tech_aid')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Ayuda Técnica')
                    ->boolean(),
                Tables\Columns\TextColumn::make('tech_aid_date')
                    ->label('Fecha Ayuda Técnica')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\IconColumn::make('nutrition_assistance')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Entrega de Alimentación')
                    ->boolean(),
                Tables\Columns\TextColumn::make('nutrition_assistance_date')
                    ->label('Fecha Entrega de Alimentación')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('diapers_size')
                    ->label('Tamaño de Pañal')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nasogastric_catheter')
                    ->label('Sonda Nasogástrica')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('urinary_catheter')
                    ->label('Sonda Urinaria')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('extra_info')
                    ->label('Otros')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('dependentCaregiver.relative')
                    ->label(new HtmlString('Parentesco <br /> <a class="font-medium text-gray-700">Cuidador</a> ')),
                Tables\Columns\TextColumn::make('dependentCaregiver.user.text')
                    ->wrap()
                    ->label(new HtmlString('Nombre <br /> <a class="font-medium text-gray-700">Cuidador</a> ')),
                Tables\Columns\TextColumn::make('dependentCaregiver.user.given')
                    ->hidden()
                    ->label(new HtmlString('Nombre Cuidador')),
                Tables\Columns\TextColumn::make('dependentCaregiver.user.fathers_family')
                    ->hidden()
                    ->label(new HtmlString('Apellido Paterno Cuidador')),
                Tables\Columns\TextColumn::make('dependentCaregiver.user.mothers_family')
                    ->hidden()
                    ->label(new HtmlString('Apellido Materno Cuidador')),
                Tables\Columns\TextColumn::make('dependentCaregiver.user.age')
                    ->label(new HtmlString('Edad  <br /> <a class="font-medium text-gray-700">Cuidador</a> ')),
                Tables\Columns\TextColumn::make('dependentCaregiver.healthcare_type')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(new HtmlString('Prevision  <br /> <a class="font-medium text-gray-700">Cuidador</a> ')),
                Tables\Columns\IconColumn::make('dependentCaregiver.empam')
                    ->label(new HtmlString('Empam <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->hidden()
                    ->boolean(),
                Tables\Columns\IconColumn::make('dependentCaregiver.zarit')
                    ->label(new HtmlString('Zarit <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->hidden()
                    ->boolean(),
                Tables\Columns\TextColumn::make('dependentCaregiver.immunizations')
                    ->hidden()
                    ->label(new HtmlString('Imunizaciones <br /> <a class="font-medium text-gray-700">Cuidador</a> ')),
                Tables\Columns\IconColumn::make('dependentCaregiver.elaborated_plan')
                    ->hidden()
                    ->label(new HtmlString('Plan Elaborado <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                Tables\Columns\IconColumn::make('dependentCaregiver.evaluated_plan')
                    ->hidden()
                    ->label(new HtmlString('Plan Evaluado <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                Tables\Columns\IconColumn::make('dependentCaregiver.trained')
                    ->hidden()
                    ->label(new HtmlString('Capacitacion <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                Tables\Columns\IconColumn::make('dependentCaregiver.stipend')
                    ->hidden()
                    ->label(new HtmlString('Estipéndio <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                Tables\Columns\TextColumn::make('dependentCaregiver.controls')
                    ->label(new HtmlString('Controles <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->badge()
                    ->separator(',')
                    ->color(fn(string $state): string => match (true) {
                        Str::contains($state, DependentCaregiver::getLabel('empam'))             => 'fuchsia',
                        Str::contains($state, DependentCaregiver::getLabel('zarit'))             => 'amber',
                        Str::contains($state, DependentCaregiver::getLabel('elaborated_plan'))   => 'sky',
                        Str::contains($state, DependentCaregiver::getLabel('evaluated_plan'))    => 'violet',
                        Str::contains($state, DependentCaregiver::getLabel('trained'))           => 'lime',
                        Str::contains($state, DependentCaregiver::getLabel('stipend'))           => 'teal',
                        Str::contains($state, DependentCaregiver::getLabel('immunizations'))           => 'orange',
                        default                                                                 => 'primary',
                    }),
            ])->striped()->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->filters([

                Tables\Filters\Filter::make('conditions_multiple')
                    ->form([
                        Forms\Components\Fieldset::make('Condiciones')
                            ->label('')
                            ->schema([
                                Forms\Components\ToggleButtons::make('tipo')
                                    ->boolean()
                                    ->default(Request::query('tipo'))
                                    ->label('Tipo')
                                    ->inline()
                                    // ->columnSpanFull()
                                    ->options([
                                        'u' => 'Union',
                                        'v' => 'Disyunción'
                                    ])
                                    ->grouped()
                                    ->colors([
                                        'false' => 'Draft',
                                        'true' => 'Success',
                                    ])
                                    ->live(), // Crucial for making the server filter react to changes

                                Forms\Components\Select::make('conditions')
                                    ->relationship('conditions', 'name', fn(Builder $query) => $query->orderByRaw('COALESCE(condition.parent_id, condition.id), condition.parent_id IS NOT NULL, condition.id'))
                                    ->placeholder('Seleccionar')
                                    ->multiple()
                                    // ->columnSpanFull()
                                    ->label('Condición')
                                    ->preload()
                                    ->hidden(fn(Get $get) => $get('tipo') == null)
                                    ->default(Request::query('conditions'))
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => is_null($record->parent_id) ? Str::ucwords($record->name) : "——" . Str::ucwords($record->name))
                            ])
                    ])
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {

                        return $query
                            ->when(
                                $data,
                                function (Builder $query, $data) {
                                    if ($data['tipo'] == 'u' && $data['conditions']) {
                                        $query->whereHas('conditions', fn($q) => $q->whereIn('condition_id', $data['conditions']));
                                    } else if ($data['tipo'] == 'v' && $data['conditions']) {
                                        foreach ($data['conditions'] as $condition_id) {
                                            $query->whereHas('conditions', fn($q) => $q->where('condition_id', $condition_id));
                                        }
                                    } else {
                                        $query;
                                    }
                                }
                            );
                    }),
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
                                $data['name'],
                                fn(Builder $query, $name): Builder => $query->whereHas('user', fn(Builder $query): Builder => $query->where('text', 'like', '%' . $name . '%')),
                            );
                    }),

                Tables\Filters\SelectFilter::make('riesgos')
                    ->label('Riesgos')
                    ->columnSpan(1)
                    ->options([
                        'Zona de Inundacion' => 'Zona de Inundación',
                        'Zona de Aluvion' => 'Zona de Aluvión'
                    ])
                    ->multiple()
                    ->default(Request::query('risks'))
                    ->query(function ($query, $data) {
                        if (! empty($data["values"])) {
                            $query->whereJsonLength('risks', '>', 0);
                            foreach ($data["values"] as $risk) {
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
                        if (! empty($data["values"])) {
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
                        })->with(['contactPoint' => function ($query) {
                            $query->has('user')->whereNotNull('contactPoint.id');
                        }, 'contactPoint.user' => function ($query) {
                            $query->has('dependentUser')->whereNotNull('contactPoint.user.id');
                        }])->pluck('alias', 'id');
                    }),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->filtersFormWidth(MaxWidth::Large)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('map')
                    ->url(fn(Model $record): string => route('filament.admin.resources.dependent-users.map', [
                        'users_id' => [$record->user?->id],
                    ]))
                    ->icon('heroicon-o-map')
                    ->label('Mapa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('map')
                        // ->url(fn(\Livewire\Component $livewire, Collection $records) => route(
                        //     'filament.admin.resources.dependent-users.map',
                        //     [
                        //         'conditions_id' => $livewire->getTable()->getFilters()['conditions']->getState('name')['values'] ?? null,
                        //         'search' => $livewire->getTable()->getFilters()['user']->getForm()->getState()['name'] ?? null,
                        //         'organizations_id' => $livewire->getTable()->getFilters()['user.mobileContactPoint.organization']->getState()['values'] ?? null,
                        //         'risks' => $livewire->getTable()->getFilters()['riesgos']->getState()['values'] ?? null,
                        //         'users_id' => $records ?? 'peo',
                        //     ]
                        // ))
                        ->action(function (\Livewire\Component $livewire, Collection $records) {
                            $ids = $records->pluck('user_id')->toArray();
                            return redirect()->route(
                                'filament.admin.resources.dependent-users.map',
                                [
                                    'conditions_multiple' => $livewire->getTableFilterState('conditions_multiple') ?? null, // INFO: NEW BEST WAY 
                                    // 'conditions_id' => $livewire->getTable()->getFilters()['conditions']->getState('name')['values'] ?? null,
                                    'search' => $livewire->getTable()->getFilters()['user']->getForm()->getState()['name'] ?? null,
                                    'organizations_id' => $livewire->getTable()->getFilters()['user.mobileContactPoint.organization']->getState()['values'] ?? null,
                                    'risks' => $livewire->getTable()->getFilters()['riesgos']->getState()['values'] ?? null,
                                    'users_id' => $ids ?? null,
                                ]
                            );
                        })
                        ->label('Mapa'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            RelationManagers\ConditionsRelationManager::class,
            // RelationManagers\DependentCaregiverRelationManager::class,
            // RelationManagers\UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDependentUsers::route('/'),
            'map' => Pages\MapDependentUsers::route('/map'),
            'create' => Pages\CreateDependentUser::route('/create'),
            'view' => Pages\ViewDependentUser::route('/{record}'),
            'edit' => Pages\EditDependentUser::route('/{record}/edit')
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('geopadds_user') || auth()->user()->hasRole('geopadds_admin') || auth()->user()->can('be god');
    }
}
