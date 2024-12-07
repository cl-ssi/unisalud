<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

use App\Models\User;
use App\Models\Country;
use App\Models\Commune;
use App\Models\Condition;
use App\Models\Organization;
use App\Models\DependentUser;
use App\Models\CodConMarital;

use App\Enums\Sex;
use App\Enums\Gender;

class CreateDependentUser extends CreateRecord
{
    protected static string $resource = DependentUserResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\toggle::make('newUser')
                    ->live()
                    ->hidden()
                    ->default(false),
                Forms\Components\Fieldset::make('User Search') 
                    ->hidden(fn(Forms\Get $get)=>$get('newUser'))
                    ->label('Buscar Usuario')
                    ->relationship('user')
                    ->schema([
                        Forms\Components\Select::make('Nombre')
                            ->placeholder('Seleccione')
                            ->label('Nombre de Usuario')
                            ->statePath('find_user')
                            ->searchable()
                            ->searchDebounce(500)
                            ->preload()
                            ->optionsLimit(10)
                            ->getSearchResultsUsing(
                                function ($search){
                                    $terms = explode(' ', $search);
                                    $query = null;
                                    foreach ($terms as $term) {
                                        if (is_null($query)) {
                                            $query = User::whereRaw("UPPER(text) LIKE '%" . trim(strtoupper($term)) . "%'");
                                        } else {
                                            $query->whereRaw("UPPER(text) LIKE '%" . trim(strtoupper($term)) . "%'");
                                        }
                                    }
                                    $query->limit(10);
                                    return $query->pluck('text', 'id');
                                }
                            )
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('create_user')
                                    ->icon('heroicon-m-user-plus')
                                    ->requiresConfirmation()
                                    ->action(fn () =>$this->data['newUser'] = true),
                            ),                        
                    ]),
                Forms\Components\Fieldset::make('Create User')
                    ->visible(fn(Forms\Get $get)=>$get('newUser'))
                    ->relationship('user')
                    ->schema([
                        Forms\Components\TextInput::make('rut')
                            ->label('RUT')
                            ->statePath('rut')
                            ->maxLength(10)
                            // ->tel()
                            // ->telRegex('^[1-9]\d*\-(\d|k|K)$')
                            ->hint('Utilizar formato: 13650969-1')
                            ->default(null),
                        Forms\Components\TextInput::make('given')
                            ->label('Nombre')
                            ->statePath('given')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('fathers_family')
                            ->label('Apellido Paterno')
                            ->statePath('fathers_family')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('mothers_family')
                            ->label('Apellido Materno')
                            ->statePath('mothers_family')
                            ->maxLength(255),
                        Forms\Components\Select::make('sex')
                            ->label('Sexo')
                            ->statePath('sex')
                            ->placeholder('Seleccione')
                            ->options(Sex::class),
                        Forms\Components\Select::make('gender')
                            ->label('Género')
                            ->statePath('gender')
                            ->placeholder('Seleccione')
                            ->options(Gender::class),
                        Forms\Components\DatePicker::make('birthday')
                            ->label('Fecha Nacimiento')
                            ->statePath('birthday'),
                        Forms\Components\Select::make('cod_con_marital_id')
                            ->label('Estado Civil')
                            ->statePath('cod_con_marital_id')
                            ->placeholder('Seleccione')
                            ->options(CodConMarital::pluck('text', 'id')),
                        Forms\Components\Select::make('nationality_id')
                            ->label('Nacionalidad')
                            ->statePath('nationality_id')
                            ->placeholder('Seleccione')
                            ->options(Country::pluck('name', 'id')),
                        Forms\Components\Select::make('commune')
                            ->label('Comuna')
                            ->statePath('commune')
                            ->placeholder('Seleccione')
                            ->options(Commune::pluck('name', 'id')),
                        Forms\Components\TextInput::make('calle')
                            ->label('Calle')
                            ->statePath('calle')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('numero')
                            ->label('Número')
                            ->statePath('numero')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('departamento')
                            ->label('Departamento')
                            ->statePath('departamento')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
