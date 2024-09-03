<?php

namespace App\Filament\Pages\Condition;

use app\Livewire\Condition\InfoUser;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions;
use Filament\Forms;
use Filament\Forms\Form;

use App\Filament\Resources\UserResource;

use App\Models\User;
use App\Models\Sex;
use App\Models\Gender;
use App\Models\Country;
use App\Models\CodConMarital;
use App\Models\DependentUser;
use App\Models\DependentConditions;
use App\Models\Condition;

class DependentUserCreate extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.condition.dependent-user-create';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public $user_id = null;

    public function mount(): void
    {
        $this->form->fill();
        // dd($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Usuario')
                        ->beforeValidation(function (Forms\Get $get) {
                            if($get('assign_type') == 1){
                                $this->user_id = $get('find_user');
                            }
                            $this->dispatch('updateUserId', $this->user_id);
                        })
                        ->schema([
                            Forms\Components\Select::make('TipoAsignacion')
                                ->selectablePlaceholder(false)
                                ->label('Tipo de Asignación')
                                ->id('assign_type') //doesnt change statePath, for the Get & Set
                                ->statePath('assign_type')
                                // ->required(fn (Forms\Get $get):bool => $get('assign_type') == 2)
                                ->live()
                                ->default(0)
                                ->options([
                                    0 => 'Seleccione',
                                    1 => 'Buscar Existente',
                                    2 => 'Nuevo',
                                ]),
                            Forms\Components\Select::make('Nombre')
                                ->placeholder('Seleccione')
                                ->label('Nombre de Usuario')
                                ->id('find_user')
                                ->statePath('find_user')
                                ->searchable()
                                ->hidden(fn (Forms\Get $get):bool => $get('assign_type') != 1)
                                ->optionsLimit(10)
                                ->options(User::pluck('text', 'id')
                            ),
                            Forms\Components\Section::make('Ingresar nuevo usuario')
                                ->hidden(fn (Forms\Get $get):bool => $get('assign_type') != 2)
                                ->schema([
                                    Forms\Components\TextInput::make('rut')
                                        // ->maxLength(255)
                                        ->maxLength(10)
                                        ->tel()
                                        ->telRegex('^[1-9]\d*\-(\d|k|K)$')
                                        ->hint('Utilizar formato: 13650969-1')
                                        ->default(null),
                                    Forms\Components\TextInput::make('given')
                                        ->label('Nombre')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('fathers_family')
                                        ->label('Apellido Paterno')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('mothers_family')
                                        ->label('Apellido Materno')
                                        ->maxLength(255),
                                    Forms\Components\Select::make('sex')
                                        ->label('Sexo')
                                        ->placeholder('Seleccione')
                                        ->options(Sex::pluck('text', 'id')),
                                    Forms\Components\Select::make('gender')
                                        ->label('Género')
                                        ->placeholder('Seleccione')
                                        ->options(Gender::pluck('text', 'id')),
                                    Forms\Components\DatePicker::make('birthday')
                                        ->label('Fecha Nacimiento'),
                                    // Forms\Components\DatePicker::make('deceased_datetime')
                                    //     ->label('Fecha Deceso'),
                                    Forms\Components\Select::make('cod_con_marital_id')
                                        ->label('Estado Civil')
                                        ->placeholder('Seleccione')
                                        ->options(CodConMarital::pluck('text', 'id')),
                                    Forms\Components\Select::make('nationality_id')
                                        ->label('Nacionalidad')
                                        ->placeholder('Seleccione')
                                        ->options(Country::pluck('name', 'id')),
                                ]),
                        ]),
                    Forms\Components\Wizard\Step::make('Dependencia')
                        ->schema([
                            Forms\Components\Livewire::make('condition.info-user'),
                            Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('diagnosis')
                                    ->label('Diagnostico'),
                                Forms\Components\DatePicker::make('check_in_date')
                                    ->label('Fecha de Ingreso'),
                                Forms\Components\DatePicker::make('check_out_date')
                                    ->label('Fecha de Egreso'),

                            ]),
                            Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('integral_visits')
                                    ->label('Vistas Integrales'),
                                Forms\Components\DatePicker::make('last_integral_visit')
                                    ->label('Última Visita Integral'),
                                Forms\Components\TextInput::make('treatment_visits')
                                    ->label('Vistas de Tratamiento'),
                                Forms\Components\DatePicker::make('last_treatment_visit')
                                    ->label('Última Visita de Tratamiento'),
                                Forms\Components\TextInput::make('barthel')
                                    ->label('Barthel'),
                                Forms\Components\TextInput::make('empam')
                                    ->label('Emp/Empam'),
                            ]),
                            Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('flood_zone')
                                    ->label('Zona de Inundabilidad'),
                                Forms\Components\Toggle::make('eleam')
                                    ->label('Eleam'),
                                Forms\Components\Toggle::make('upp')
                                    ->label('UPP'),
                                ]),
                            Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('elaborated_plan')
                                    ->label('Plan Elaborado'),
                                Forms\Components\Toggle::make('evaluated_plan')
                                    ->label('Plan Evaluado'),
                            ]),
                            Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('pneumonia')
                                    ->label('Neumonia'),
                                Forms\Components\TextInput::make('influenza')
                                    ->label('Influenza'),
                                Forms\Components\TextInput::make('covid_19')
                                    ->label('Covid-19'),
                                Forms\Components\DatePicker::make('covid_19_date')
                                    ->label('Fecha de Covid-19'),
                                Forms\Components\TextInput::make('extra_info')
                                    ->label('Otros'),
                                Forms\Components\TextInput::make('tech_aid')
                                    ->label('Ayuda Técnica'),
                                Forms\Components\DatePicker::make('tech_aid_date')
                                    ->label('Fecha Ayuda Técnica'),
                                Forms\Components\TextInput::make('nutrition_assistance')
                                    ->label('Entrega de Alimentación'),
                                Forms\Components\DatePicker::make('nutrition_assistance_date')
                                    ->label('Fecha Entrega de Alimentación'),
                            ])
                        ]),
                ])
            ])->statePath('data');
    }
}

