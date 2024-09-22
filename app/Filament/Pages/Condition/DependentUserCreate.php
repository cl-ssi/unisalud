<?php

namespace App\Filament\Pages\Condition;

use app\Livewire\Condition\InfoUser;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use App\Services\GeocodingService;

use App\Filament\Resources\UserResource;

use App\Enums\Sex;
use App\Enums\Gender;

use App\Models\User;
// use App\Models\Sex;
// use App\Models\Gender;
use App\Models\Country;
use App\Models\Commune;
use App\Models\Address;
use App\Models\Location;
use App\Models\HumanName;
use App\Models\Condition;
use App\Models\Identifier;
use App\Models\CodConMarital;
use App\Models\DependentUser;
use App\Models\DependentConditions;

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
                            } else if ($get('assign_type') == 2){
                                list($run,$dv) = array_pad(explode('-',str_replace(".", "", $get('rut'))),2,null);
                                // $sex = Sex::find($get('sex'))->text;
                                // $gender = Gender::find($get('gender'))->text;

                                $user = User::whereHas('identifiers', function ($query) use($run) {
                                    $query->where('value', $run)
                                        ->Where('cod_con_identifier_type_id', 1);
                                    })
                                    ->first();
                                $userCreatedOrUpdated = User::updateOrCreate(
                                    [
                                        'id'    => $user ? $user->id : null
                                    ]
                                    ,
                                    [
                                        'active'                => 1,
                                        'text'                  => $get('given').' '.$get('fathers_family').' '.$get('mothers_family'),
                                        'given'                 => $get('given'),
                                        'fathers_family'        => $get('fathers_family'),
                                        'mothers_family'        => $get('mothers_family'),
                                        'sex'                   => $get('sex'),
                                        'gender'                => $get('gender'),
                                        'birthday'              => date("Y-m-d", strtotime($get('birthday'),)),
                                        // 'cod_con_marital_id'    => $this->originalData['estado_civil'],
                                        'nationality_id'        => $get('nationality_id'),
                                    ]
                                );

                                if($user == null){
                                    $identifierCreated = Identifier::create(
                                        [
                                            'user_id'                       => $userCreatedOrUpdated->id,
                                            'use'                           => 'official',
                                            'cod_con_identifier_type_id'    => 1,
                                            'value'                         => $run,
                                            'dv'                            => $dv
                                        ]
                                    );
                                    $humanNameCreated = HumanName::create(
                                        [
                                            'use'               => 'official',
                                            'given'             => $get('given'),
                                            'fathers_family'    => $get('fathers_family'),
                                            'mothers_family'    => $get('mothers_family'),
                                            'period_start'      => now(),
                                            'user_id'           => $userCreatedOrUpdated->id
                                        ]
                                    );
                                    $commune = Commune::where('id', $get('commune'))->first();

                                    $addressCreated = Address::create(
                                        [
                                            'user_id'       => $userCreatedOrUpdated->id,
                                            'use'           => 'home',
                                            'type'          => 'physical',
                                            'text'          => $get('calle'),
                                            'line'          => $get('numero'),
                                            'apartment'     => $get('departamento'),
                                            'suburb'        => null,
                                            'city'          => null,
                                            'commune_id'    => $commune->id,
                                            'postal_code'   => null,
                                            'region_id'     => null,
                                        ]
                                    );

                                    $latitude   = null;
                                    $longitude  = null;

                                    $locationExist = new Location();
                                    $locationExist = $addressCreated->location ? $addressCreated->location : null;

                                    $calle = $get('calle');
                                    $numero = $get('numero');
                                    $comuna = $commune->name;

                                    if ($calle && $numero && $comuna ) {

                                        $geocodingService = app(GeocodingService::class);
                                        $coordinates = $geocodingService->getCoordinates($calle.'+'.$numero.'+'.$comuna);

                                        if ($coordinates) {
                                            $latitude   = $coordinates['lat'];
                                            $longitude  = $coordinates['lng'];
                                        }
                                    }

                                    $locationCreatedOrUpdated = Location::updateOrCreate(
                                        [
                                            'id'    => $addressCreated->location ? $addressCreated->location->id : null
                                        ]
                                        ,
                                        [
                                            'address_id'        => $addressCreated->id,
                                            'longitude'         => $longitude,
                                            'latitude'          => $latitude
                                        ]
                                    );
                                }
                                $this->user_id = $userCreatedOrUpdated->id;

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
                                    // Forms\Components\Select::make('cod_con_marital_id')
                                    //     ->label('Estado Civil')
                                    //     ->statePath('cod_con_marital_id')
                                    //     ->placeholder('Seleccione')
                                    //     ->options(CodConMarital::pluck('text', 'id')),
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
                        ]),
                    Forms\Components\Wizard\Step::make('Dependencia')
                        ->schema([
                            Forms\Components\Livewire::make('condition.info-user', fn()=>['user_id' => $this->user_id]),
                            Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('condition')
                                ->label('Condicion')
                                ->statePath('condition')
                                ->placeholder('Seleccione')
                                ->options(Condition::pluck('name', 'id')),
                            ]),
                            Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('diagnosis')
                                    ->statePath('diagnosis')
                                    ->label('Diagnostico'),
                                Forms\Components\DatePicker::make('check_in_date')
                                    ->statePath('check_in_date')
                                    ->label('Fecha de Ingreso'),
                                Forms\Components\DatePicker::make('check_out_date')
                                    ->statePath('check_out_date')
                                    ->label('Fecha de Egreso'),

                            ]),
                            Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('integral_visits')
                                    ->statePath('integral_visits')
                                    ->label('Vistas Integrales'),
                                Forms\Components\DatePicker::make('last_integral_visit')
                                    ->statePath('last_integral_visit')
                                    ->label('Última Visita Integral'),
                                Forms\Components\TextInput::make('treatment_visits')
                                    ->statePath('treatment_visits')
                                    ->label('Vistas de Tratamiento'),
                                Forms\Components\DatePicker::make('last_treatment_visit')
                                    ->statePath('last_treatment_visit')
                                    ->label('Última Visita de Tratamiento'),
                                Forms\Components\TextInput::make('barthel')
                                    ->statePath('barthel')
                                    ->label('Barthel'),
                                Forms\Components\TextInput::make('empam')
                                    ->statePath('empam')
                                    ->label('Emp/Empam'),
                            ]),
                            Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('flood_zone')
                                    ->statePath('flood_zone')
                                    ->label('Zona de Inundabilidad'),
                                Forms\Components\Toggle::make('eleam')
                                    ->statePath('eleam')
                                    ->label('Eleam'),
                                Forms\Components\Toggle::make('upp')
                                    ->statePath('upp')
                                    ->label('UPP'),
                                ]),
                            Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('elaborated_plan')
                                    ->statePath('elaborated_plan')
                                    ->label('Plan Elaborado'),
                                Forms\Components\Toggle::make('evaluated_plan')
                                    ->statePath('evaluated_plan')
                                    ->label('Plan Evaluado'),
                            ]),
                            Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('pneumonia')
                                    ->statePath('pneumonia')
                                    ->label('Neumonia'),
                                Forms\Components\TextInput::make('influenza')
                                    ->statePath('influenza')
                                    ->label('Influenza'),
                                Forms\Components\TextInput::make('covid_19')
                                    ->statePath('covid_19')
                                    ->label('Covid-19'),
                                Forms\Components\DatePicker::make('covid_19_date')
                                    ->statePath('covid_19_date')
                                    ->label('Fecha de Covid-19'),
                                Forms\Components\TextInput::make('extra_info')
                                    ->statePath('extra_info')
                                    ->label('Otros'),
                                Forms\Components\TextInput::make('tech_aid')
                                    ->statePath('tech_aid')
                                    ->label('Ayuda Técnica'),
                                Forms\Components\DatePicker::make('tech_aid_date')
                                    ->statePath('tech_aid_date')
                                    ->label('Fecha Ayuda Técnica'),
                                Forms\Components\TextInput::make('nutrition_assistance')
                                    ->statePath('nutrition_assistance')
                                    ->label('Entrega de Alimentación'),
                                Forms\Components\DatePicker::make('nutrition_assistance_date')
                                    ->statePath('nutrition_assistance_date')
                                    ->label('Fecha Entrega de Alimentación'),
                            ]),

                        ]),
                ])
                ->nextAction(fn(Forms\Components\Actions\Action $action)=>$action->label('Siguiente'))
                ->submitAction(
                    new HtmlString(
                        Blade::render(
                            <<<BLADE
                                <x-filament::button
                                    type="submit"
                                    class="mt-6 w-full"
                                >
                                    <span class="block" wire:loading.class="hidden"> Guardar </span>
                                    <span class="hidden" wire:loading wire:loading.class="block">Guardando...</span>
                                </x-filament::button>
                            BLADE
                        )
                    )
                )
            ])->statePath('data');
    }

    public function save(){

        $this->form->disabled(true);
        $formData = $this->form->getState();

        // Se busca la Condition mediante el nombre de la condicion
        $condition = Condition::where('id', '=', $formData['condition'])->firstOrFail();

        $dependentUser = DependentUser::create(
            [
                'user_id'                      => $this->user_id,
                'cod_con_clinical_status'      => 'active',
                'cod_con_verification_status'  => 'confirmed',
                'diagnosis' => $formData['diagnosis'],
                'check_in_date' => $formData['check_in_date'],
                'check_out_date' => $formData['check_out_date'],
                'integral_visits' => $formData['integral_visits'],
                'treatment_visits' => $formData['treatment_visits'],
                'last_integral_visit' => $formData['last_integral_visit'],
                'last_treatment_visit' => $formData['last_treatment_visit'],
                'barthel' => $formData['barthel'],
                'empam' => $formData['empam'],
                'eleam' => $formData['eleam'],
                'upp' => $formData['upp'],
                'elaborated_plan' => $formData['elaborated_plan'],
                'evaluated_plan' => $formData['evaluated_plan'],
                'pneumonia' => $formData['pneumonia'],
                'influenza' => $formData['influenza'],
                'covid_19' => $formData['covid_19'],
                'covid_19_date' => $formData['covid_19_date'],
                'extra_info' => $formData['extra_info'],
                'tech_aid' => $formData['tech_aid'],
                'tech_aid_date' => $formData['tech_aid_date'],
                'nutrition_assistance' => $formData['nutrition_assistance'],
                'nutrition_assistance_date' => $formData['nutrition_assistance_date'],
                'flood_zone' => $formData['flood_zone'],
            ]
        );

        // Se crea la relacion en la tabla pivote DependentConditions
        DependentConditions::firstOrCreate(['dependent_user_id' => $dependentUser->id, 'condition_id' => $condition->id]);
        Notification::make()->title('Cambios guardados satisfactoriamente.')->success()->send();
            return redirect()
            ->to(route('filament.admin.pages.dependent-user-list'));
    }
}

