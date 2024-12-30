<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DependentUserResource\Pages;
use App\Filament\Resources\DependentUserResource\RelationManagers;
use App\Filament\Imports\ConditionImporter;

use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;

use Filament\Tables\Table;


use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Models\User;
use App\Models\Country;
use App\Models\Commune;
use App\Models\Condition;
use App\Models\Organization;
use App\Models\DependentUser;

use App\Enums\Sex;
use App\Enums\Gender;

use Carbon\Carbon;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Actions\ActionGroup;

class DependentUserResource extends Resource
{
    protected static ?string $model = DependentUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Usuario Dependiente';

    protected static ?string $pluralModelLabel = 'Usuarios Dependiente';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([                             
                Forms\Components\Fieldset::make('Usuario Dependiente')
                    ->relationship('user')
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
                                Forms\Components\TextInput::make('value')
                                    ->formatStateUsing(fn (Model $record): string => $record->value . '-' . $record->dv)
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
                    ->relationship('dependentCaregiver')
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(2)
                            ->columnSpan('full')
                            ->relationship('user')
                            ->schema([
                                Forms\Components\TextInput::make('text')
                                    ->label('Nombre')
                                    ->disabled(),
                                Forms\Components\DatePicker::make('birthday')
                                    ->label('Edad')
                                    ->disabled(),
                                Forms\Components\Group::make()
                                    ->relationship('officialIdentifier')
                                    ->schema([
                                        Forms\Components\TextInput::make('value')
                                            ->formatStateUsing(fn (Model $record): string => $record->value . '-' . $record->dv)
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
                    // ->columns(3)
                    // ->columnSpan('full')
                    ->relationship('user')
                    ->schema([
                        Forms\Components\Group::make()
                            ->columns(3)
                            ->columnSpan('full')
                            ->relationship('address')
                            ->schema([
                                Forms\Components\TextInput::make('text')
                                    ->label('Calle'),
                                Forms\Components\TextInput::make('line')
                                    ->label('Número'),
                                Forms\Components\Select::make('commune')
                                    ->relationship(titleAttribute: 'name')
                                    ->label('Ciudad'),
                                // Map::make('location')
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
                    ->numeric(),
                Forms\Components\DatePicker::make('last_integral_visit')
                    ->label('Última Visita Integral'),
                Forms\Components\TextInput::make('treatment_visits')
                    ->label('Visitas de Tratamiento')
                    ->numeric(),
                Forms\Components\DatePicker::make('last_treatment_visit')
                    ->label('Última Visita de Tratamiento'),
                Forms\Components\Select::make('barthel')
                    ->options([
                        'independent'=> 'Independiente',
                        'slight'=> 'Leve',
                        'moderate'=> 'Moderado',
                        'severe'=> 'Grave',
                        'total'=> 'Total',
                    ]),
                /*                 
                Forms\Components\ToggleButtons::make('Aplicables')
                    ->multiple()
                    ->boolean()
                    ->inline()
                    ->options([
                        'empam'=> 'Empam',
                        'eleam'=> 'Eleam',
                        'upp'=> 'UPP',
                        'elaborated_plan'=> 'Plan Elaborado',
                        'evaluated_plan'=> 'Plan Evaluado',
                    ]), 
                */
                /*  
                Forms\Components\TagsInput::make('tags')
                    ->suggestions([
                        'empam',
                        'eleam',
                        'upp',
                        'elaborated_plan',
                        'evaluated_plan',
                    ]),
                */
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
            ->columns([
                Tables\Columns\TextColumn::make('dependentCaregiver.user.mobileContactPoint.organization.alias')
                    ->label('Establecimiento'),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Nombre Completo')
                    ->getStateUsing(function ($record) {
                        return ($record->user->text)??$record->user->given . ' ' . $record->user->fathers_family . ' ' . $record->user->mothers_family;
                    }),
                Tables\Columns\TextColumn::make('user.sex')
                    ->label('Sexo'),
                Tables\Columns\TextColumn::make('user.gender')
                    ->label('Genero'),
                Tables\Columns\TextColumn::make('user.birthday')
                    ->label('Fecha Nacimiento')
                    ->date('Y-m-d'),
                Tables\Columns\TextColumn::make('age')
                    ->label('Edad')
                    ->getStateUsing(fn ($record) =>
                     $record->user->birthday->age
                    ),
                Tables\Columns\TextColumn::make('user.address.use')
                    ->label('Tipo Dirección'),
                Tables\Columns\TextColumn::make('user.address.text')
                    ->label('Calle'),
                Tables\Columns\TextColumn::make('user.address.line')
                    ->label('N°'),
                Tables\Columns\TextColumn::make('user.address.commune.name')
                    ->label('Comuna'),
                Tables\Columns\TextColumn::make('user.address.location.longitude')
                    ->label('Longitud'),
                Tables\Columns\TextColumn::make('user.address.location.latitude')
                    ->label('Latitud'),
                Tables\Columns\TextColumn::make('user.mobileContactPoint.value')
                    ->label('Telefono'),
                Tables\Columns\TextColumn::make('check_in_date')
                    ->label('Fecha de Ingreso')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_date')
                    ->label('Fecha de Egreso')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('integral_visits')
                    ->label('Vistas Integrales')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_integral_visit')
                    ->label('Última Visita Integral')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('treatment_visits')
                    ->label('Visitas de Tratamiento')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_treatment_visit')
                    ->label('Última Visita de Tratamiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barthel')
                    ->label('Barthel'),
                /*
                Tables\Columns\IconColumn::make('empam')
                    ->label('Emp / Empam')
                    ->boolean(),
                Tables\Columns\IconColumn::make('eleam')
                    ->label('Eleam')
                    ->boolean(),
                Tables\Columns\IconColumn::make('upp')
                    ->label('UPP')
                    ->boolean(),
                Tables\Columns\IconColumn::make('elaborated_plan')
                    ->label('Plan Elaborado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('evaluated_plan')
                    ->label('Plan Evaluado')
                    ->boolean(),
                */
                Tables\Columns\TextColumn::make('pneumonia')
                    ->label('Neumonia')
                    ->date()
                    ->searchable(),
                Tables\Columns\TextColumn::make('influenza')
                    ->label('Influenza')
                    ->date()
                    ->searchable(),
                Tables\Columns\TextColumn::make('covid_19')
                    ->label('Covid-19')
                    ->date()
                    ->searchable(),
                Tables\Columns\IconColumn::make('tech_aid')
                    ->label('Ayuda Técnica')
                    ->boolean(),
                Tables\Columns\TextColumn::make('tech_aid_date')
                    ->label('Fecha Ayuda Técnica')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('nutrition_assistance')
                    ->label('Entrega de Alimentación')
                    ->boolean(),
                Tables\Columns\TextColumn::make('nutrition_assistance_date')
                    ->label('Fecha Entrega de Alimentación')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nasogastric_catheter')
                    ->label('Sonda Nasogástrica'),
                Tables\Columns\TextColumn::make('urinary_catheter')
                    ->label('Sonda Urinaria'),
                Tables\Columns\IconColumn::make('flood_zone')
                    ->label('Zona de Inundabilidad')
                    ->boolean(),
                Tables\Columns\TextColumn::make('extra_info')
                    ->label('Otros'),
                Tables\Columns\TextColumn::make('dependentCaregiver.relative')
                    ->label(new HtmlString('Parentesco <br /> <a class="font-medium text-gray-700">Cuidador</a> ')),
                    // ->label(''),
                Tables\Columns\TextColumn::make('dependentCaregiver.user.text')
                    ->label(new HtmlString('Nombre <br /> <a class="font-medium text-gray-700">Cuidador</a> ')),
                Tables\Columns\TextColumn::make('dependentCaregiver.user.age')
                    ->label(new HtmlString('Edad  <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->getStateUsing(function ($record) {
                        return Carbon::parse($record->dependentCaregiver->user->birthday)->age;
                    }),
                Tables\Columns\IconColumn::make('dependentCaregiver.empam')
                    ->label(new HtmlString('Empam <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                Tables\Columns\IconColumn::make('dependentCaregiver.zarit')
                    ->label(new HtmlString('Zarit <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                Tables\Columns\TextColumn::make('.dependentCaregiver.immunizations')
                    ->label(new HtmlString('Imunizacion <br /> <a class="font-medium text-gray-700">Cuidador</a> ')),
                Tables\Columns\IconColumn::make('dependentCaregiver.elaborated_plan')
                    ->label(new HtmlString('Plan Elaborado <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                Tables\Columns\IconColumn::make('dependentCaregiver.evaluated_plan')
                    ->label(new HtmlString('Plan Evaluado <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                Tables\Columns\IconColumn::make('dependentCaregiver.trained')
                    ->label(new HtmlString('Plan Evaluado <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                Tables\Columns\IconColumn::make('dependentCaregiver.stipend')
                    ->label(new HtmlString('Plan Evaluado <br /> <a class="font-medium text-gray-700">Cuidador</a> '))
                    ->boolean(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('deleted_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                /*
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Tipo de Condición')
                    ->placeholder('Seleccione')
                    ->options(Condition::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, $state){
                        return $query->when(
                            $state,
                            fn (Builder $query): Builder => $query->whereHas('conditions', fn (Builder $query): Builder => $query->where('condition_id', '=', $state)),
                            fn (Builder $query): Builder => $query->whereNull('id')
                        );
                    }), 
                */
                Tables\Filters\Filter::make('user')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->statePath('name')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                        ->when(
                            $data['name'],
                            fn (Builder $query, $name): Builder => $query->whereHas('user', fn (Builder $query): Builder => $query->where('text', 'like', '%' . $name . '%')),
                        );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('map')
                ->label('')
                ->icon('heroicon-s-map')
                ->url(fn (Model $record): string => route('filament.admin.pages.dependent-user-map', ['condition_id' => $record->conditions->first()->id, 'user_id' => $record->user->id]))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                /*                 
                Tables\Actions\ImportAction::make()
                    ->importer(ConditionImporter::class)
                    ->label('Importar Condición de Usuarios')
                    ->modalHeading('Importar Condición de Usuarios')
                    // ->modalDescription('Subir archivo CSV')
                    ->modalSubmitActionLabel('Importar')
                    // ->options([]) 
                    */
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ConditionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDependentUsers::route('/'),
            'create' => Pages\CreateDependentUser::route('/create'),
            'edit' => Pages\EditDependentUser::route('/{record}/edit'),
        ];
    }

    public static function viewAny(): bool  {
        // return true;
        return auth()->user()->can('be god'); 
    }
}
