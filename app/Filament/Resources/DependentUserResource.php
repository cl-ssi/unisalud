<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DependentUserResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\DependentUser;
use App\Models\User;
use App\Models\Condition;

use Carbon\Carbon;

class DependentUserResource extends Resource
{
    protected static ?string $model = DependentUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('identifier')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('cod_con_clinical_status'),
                // Forms\Components\TextInput::make('cod_con_verification_status'),
                // Forms\Components\TextInput::make('cod_con_code_id')
                //     ->numeric(),
                // Forms\Components\Select::make('user_id')
                //     ->relationship('user', 'id'),
                Forms\Components\Textarea::make('diagnosis')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('check_in_date'),
                Forms\Components\DatePicker::make('check_out_date'),
                Forms\Components\TextInput::make('integral_visits')
                    ->numeric(),
                Forms\Components\DatePicker::make('last_integral_visit'),
                Forms\Components\TextInput::make('treatment_visits')
                    ->numeric(),
                Forms\Components\DatePicker::make('last_treatment_visit'),
                Forms\Components\TextInput::make('barthel')
                    ->maxLength(255),
                Forms\Components\TextInput::make('empam')
                    ->maxLength(255),
                Forms\Components\Toggle::make('eleam'),
                Forms\Components\Toggle::make('upp'),
                Forms\Components\Toggle::make('elaborated_plan'),
                Forms\Components\Toggle::make('evaluated_plan'),
                Forms\Components\TextInput::make('pneumonia')
                    ->maxLength(255),
                Forms\Components\TextInput::make('influenza')
                    ->maxLength(255),
                Forms\Components\TextInput::make('covid_19')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('covid_19_date'),
                Forms\Components\Textarea::make('extra_info')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('tech_aid'),
                Forms\Components\DatePicker::make('tech_aid_date'),
                Forms\Components\Toggle::make('nutrition_assistance'),
                Forms\Components\DatePicker::make('nutrition_assistance_date'),
                Forms\Components\Toggle::make('flood_zone'),                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->date(),
                Tables\Columns\TextColumn::make('age')
                    ->label('Edad')
                    ->getStateUsing(function ($record) {
                        return Carbon::parse($record->user->birthday)->age;
                    }),
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
                    ->label('Barthel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('empam')
                    ->label('Emp / Empam')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('pneumonia')
                    ->label('Neumonia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('influenza')
                    ->label('Influenza')
                    ->searchable(),
                Tables\Columns\TextColumn::make('covid_19')
                    ->label('Covid-19')
                    ->searchable(),
                Tables\Columns\TextColumn::make('covid_19_date')
                    ->label('Fecha de Covid-19')
                    ->date()
                    ->sortable(),
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
                    ->label(new HtmlString('Edad  <br /> <a class="font-medium text-gray-700">Cuidador</a> ')),
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
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Tipo de Condición')
                    ->placeholder('Seleccione')
                    ->options(Condition::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, $state){
                        return $query->when(
                            $state,
                            fn (Builder $query): Builder => $query->whereHas('dependentConditions', fn (Builder $query): Builder => $query->where('condition_id', '=', $state)),
                            fn (Builder $query): Builder => $query->whereNull('id')
                        );
                    }),
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
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // 
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
