<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\DependentUserResource;
use Illuminate\Database\Eloquent\Model;


use App\Models\DependentUser;
use App\Models\DependentCaregiver;

use App\Models\User;
use App\Models\Identifier;
use App\Models\HumanName;
use App\Models\Address;
use App\Services\GeocodingService;
use App\Models\Commune;
use App\Models\ContactPoint;
use App\Models\Location;

// use App\Models\Coding;

use App\Models\Sex as ClassSex;
use App\Models\Gender as ClassGender;
use App\Models\Country;
use App\Models\Organization;
use DateTime;
use Carbon\Carbon;



class CreateDependentUser extends CreateRecord
{
    protected static string $resource = DependentUserResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Fieldset::make('Datos del Paciente')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('apellido_paterno')
                            ->label('Apellido Paterno')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('apellido_materno')
                            ->label('Apellido Materno')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('run')
                            ->label('RUN')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('dv')
                            ->label('DV')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('fecha_nacimiento')
                            ->label('Fecha de Nacimiento')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('sexo')
                            ->label('Sexo')
                            ->options([
                                'male' => 'Masculino',
                                'female' => 'Femenino',
                                'other' => 'Otro',
                                'unknown' => 'Desconocido',
                            ])
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('genero')
                            ->label('Género')
                            ->options([
                                'female' => 'Mujer',
                                'male' => 'Hombre',
                                'non-binary' => 'No binario',
                                'transgender-male' => 'Transgénero masculino',
                                'transgender-female' => 'Transgénero femenino',
                                'other' => 'Otro',
                                'non-disclose' => 'No revelar',
                            ])
                            ->columnSpan(1),
                        Forms\Components\Select::make('nacionalidad')
                            ->label('Nacionalidad')
                            ->options([
                                'chileno' => 'Chileno',
                                'extranjero' => 'Extranjero',
                            ])
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('comuna')
                            ->label('Comuna')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('calle')
                            ->label('Calle')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('numero')
                            ->label('Número')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('departamento')
                            ->label('Departamento')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('telefono')
                            ->label('Teléfono')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('establecimiento')
                            ->label('Establecimiento')
                            ->columnSpan(1),
                        Forms\Components\Select::make('prevision')
                            ->label('Previsión')
                            ->options([
                                'FONASA A',
                                'FONASA B',
                                'FONASA C',
                                'FONASA D',
                                'ISAPRE',
                                'PRAIS'
                            ])
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('diagnostico')
                            ->label('Diagnóstico')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('fecha_ingreso')
                            ->label('Fecha de Ingreso')
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('fecha_egreso')
                            ->label('Fecha de Egreso')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('visitas_integrales')
                            ->label('Visitas Integrales')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('visitas_tratamiento')
                            ->label('Visitas Tratamiento')
                            ->columnSpan(1),

                        Forms\Components\Select::make('barthel')
                            ->label('EMP EMPAM')
                            ->columnSpan(1)
                            ->options([
                                'independent' => 'Independiente',
                                'slight'  => 'Leve',
                                'moderate' => 'Moderado',
                                'severe' => 'Severo',
                                'total' => 'Total',
                            ]),
                        Forms\Components\Toggle::make('emp_empam')
                            ->label('EMP EMPAM')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('eleam')
                            ->label('ELEAM')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('upp')
                            ->label('UPP')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('plan_elaborado')
                            ->label('Plan Elaborado')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('plan_evaluado')
                            ->label('Plan Evaluado')
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('neumo')
                            ->label('Neumo')
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('influenza')
                            ->label('Influenza')
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('covid_19')
                            ->label('COVID-19')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('ayuda_tecnica')
                            ->label('Ayuda Técnica')
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('ayuda_tecnica_fecha')
                            ->label('Fecha Ayuda Técnica')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('entrega_alimentacion')
                            ->label('Entrega Alimentación')
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('entrega_alimentacion_fecha')
                            ->label('Fecha Entrega Alimentación')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('talla_panal')
                            ->label('Talla Pañal')
                            ->columnSpan(1),
                        Forms\Components\Select::make('sonda_sng')
                            ->label('Sonda SNG')
                            ->options([
                                '10',
                                '12',
                                '14',
                                '16',
                                '18',
                                '20',
                            ])
                            ->columnSpan(1),
                        Forms\Components\Select::make('sonda_urinaria')
                            ->label('Sonda Urinaria')
                            ->options([
                                '12',
                                '14',
                                '16',
                                '18',
                                '20',
                                '22',
                                '24',
                            ])
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('extra_info')
                            ->label('Información Extra')
                            ->columnSpan(1),
                    ]),

                Forms\Components\Fieldset::make('Datos del Cuidador')
                    // ->visible(fn() => $this->data['hasCaregiver'] ?? false)
                    ->schema([
                        Forms\Components\TextInput::make('nombre_cuidador')
                            ->label('Nombre Cuidador')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('apellido_paterno_cuidador')
                            ->label('Apellido Paterno Cuidador')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('apellido_materno_cuidador')
                            ->label('Apellido Materno Cuidador')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('fecha_nacimiento_cuidador')
                            ->label('Fecha de Nacimiento Cuidador')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('run_cuidador')
                            ->label('RUN Cuidador')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('dv_cuidador')
                            ->label('DV Cuidador')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('sexo_cuidador')
                            ->label('Sexo Cuidador')
                            ->options([
                                'masculino' => 'Masculino',
                                'femenino' => 'Femenino',
                            ])
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('genero_cuidador')
                            ->label('Género Cuidador')
                            ->options([
                                'masculino' => 'Masculino',
                                'femenino' => 'Femenino',
                            ])
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('nacionalidad_cuidador')
                            ->label('Nacionalidad Cuidador')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('parentesco_cuidador')
                            ->label('Parentesco Cuidador')
                            ->options([
                                'esposo' => 'Conjuge',
                                'hijo' => 'Hijo/a',
                                'madrastro' => 'Cuidador Pagado',
                                'otro' => 'Otro',
                            ])
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('prevision_cuidador')
                            ->label('Previsión Cuidador')
                            ->options([
                                'Isapre' => 'Isapre',
                                'Fonasa' => 'Fonasa',
                            ])
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('empam_cuidador')
                            ->label('EMPAM Cuidador')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('zarit_cuidador')
                            ->label('Zarit Cuidador')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('inmunizaciones_cuidador')
                            ->label('Inmunizaciones Cuidador')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('plan_elaborado_cuidador')
                            ->label('Plan Elaborado Cuidador')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('plan_evaluado_cuidador')
                            ->label('Plan Evaluado Cuidador')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('capacitacion_cuidador')
                            ->label('Capacitación Cuidador')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('estipendio_cuidador')
                            ->label('Estipendio Cuidador')
                            ->columnSpan(1),
                    ]),
            ]);
    }

    protected function handleRecordCreation(array $data): Model
    {

        $this->data['nombre'] = $this->data['nombre'] ?? '';
        $this->data['apellido_paterno'] = $this->data['apellido_paterno'] ?? '';
        $this->data['apellido_materno'] = $this->data['apellido_materno'] ?? '';
        $this->data['run'] = $this->data['run'] ?? '';
        $this->data['dv'] = $this->data['dv'] ?? '';


        $this->data['sexo'] = $this->data['sexo'] ?? '';
        $this->data['genero'] = $this->data['genero'] ?? '';

        $this->data['fecha_nacimiento'] = $this->data['fecha_nacimiento'] ?? null;
        $this->data['nacionalidad'] = $this->data['nacionalidad'] ?? '';
        $this->data['comuna'] = $this->data['comuna'] ?? '';
        $this->data['calle'] = $this->data['calle'] ?? '';
        $this->data['departamento'] = $this->data['departamento'] ?? '';
        $this->data['establecimiento'] = $this->data['establecimiento'] ?? '';
        $this->data['prevision'] = $this->data['prevision'] ?? '';

        $this->data['diagnostico'] = $this->data['diagnostico'] ?? '';
        $this->data['fecha_ingreso'] = $this->data['fecha_ingreso'] ?? null;
        $this->data['fecha_egreso'] = $this->data['fecha_egreso'] ?? null;
        $this->data['visitas_integrales'] = $this->data['visitas_integrales'] ?? null;
        $this->data['fecha_visita_integral'] = $this->data['fecha_visita_integral'] ?? null;
        $this->data['visitas_tratamiento'] = $this->data['visitas_tratamiento'] ?? null;
        $this->data['fecha_visita_tratamiento'] = $this->data['fecha_visita_tratamiento'] ?? null;
        $this->data['barthel'] = $this->data['barthel'] ?? null;
        $this->data['emp_empam'] = $this->data['emp_empam'] ?? null;
        $this->data['eleam'] = $this->data['eleam'] ?? null;
        $this->data['upp'] = $this->data['upp'] ?? null;
        $this->data['plan_elaborado'] = $this->data['plan_elaborado'] ?? null;
        $this->data['plan_evaluado'] = $this->data['plan_evaluado'] ?? null;
        $this->data['neumo'] = $this->data['neumo'] ?? null;
        $this->data['influenza'] = $this->data['influenza'] ?? null;
        $this->data['covid_19'] = $this->data['covid_19'] ?? null;
        $this->data['ayuda_tecnica'] = $this->data['ayuda_tecnica'] ?? null;
        $this->data['ayuda_tecnica_fecha'] = $this->data['ayuda_tecnica_fecha'] ?? '';
        $this->data['entrega_alimentacion'] = $this->data['entrega_alimentacion'] ?? null;
        $this->data['entrega_alimentacion_fecha'] = $this->data['entrega_alimentacion_fecha'] ?? '';
        $this->data['talla_panal'] = $this->data['talla_panal'] ?? '';
        $this->data['sonda_sng'] = $this->data['sonda_sng'] ?? '';
        $this->data['sonda_urinaria'] = $this->data['sonda_urinaria'] ?? '';
        $this->data['extra_info'] = $this->data['extra_info'] ?? null;

        $this->data['prevision_cuidador'] = $this->data['prevision_cuidador'] ?? '';
        $this->data['nombre_cuidador'] = $this->data['nombre_cuidador'] ?? '';
        $this->data['apellido_paterno_cuidador'] = $this->data['apellido_paterno_cuidador'] ?? '';
        $this->data['apellido_paterno_cuidador'] = $this->data['apellido_materno_cuidador'] ?? '';
        $this->data['fecha_nacimiento_cuidador'] = $this->data['fecha_nacimiento_cuidador'] ?? null;
        $this->data['run_cuidador'] = $this->data['run_cuidador'] ?? '';
        $this->data['dv_cuidador'] = $this->data['dv_cuidador'] ?? '';
        $this->data['sexo_cuidador'] = $this->data['sexo_cuidador'] ?? '';
        $this->data['genero_cuidador'] = $this->data['genero_cuidador'] ?? '';
        $this->data['nacionalidad_cuidador'] = $this->data['nacionalidad_cuidador'] ?? '';
        $this->data['parentesco_cuidador'] = $this->data['parentesco_cuidador'] ?? '';
        $this->data['empam_cuidador'] = $this->data['empam_cuidador'] ?? '';
        $this->data['zarit_cuidador'] = $this->data['zarit_cuidador'] ?? '';
        $this->data['inmunizaciones_cuidador'] = $this->data['inmunizaciones_cuidador'] ?? '';
        $this->data['plan_elaborado_cuidador'] = $this->data['plan_elaborado_cuidador'] ?? '';
        $this->data['plan_evaluado_cuidador'] = $this->data['plan_evaluado_cuidador'] ?? '';
        $this->data['capacitacion_cuidador'] = $this->data['capacitacion_cuidador'] ?? '';
        $this->data['estipendio_cuidador'] = $this->data['estipendio_cuidador'] ?? '';

        //PRIMERO VERIFICAR SI EXITE EL USER
        $user = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->data['run'])->Where('cod_con_identifier_type_id', 1);
        })->first();

        $sexValue = ClassSex::where('text', $this->data['sexo'])->first()->value ?? null;
        $sexGender = ClassGender::where('text', $this->data['genero'])->first()->value ?? null;
        $nationality = Country::where('name', $this->data['nacionalidad'])->first()->id ?? null;

        $userCreatedOrUpdated = User::updateOrCreate(
            [
                'id'    => $user ? $user->id : null
            ],
            [
                'active'                => 1,
                'text'                  => $this->data['nombre'] . ' ' . $this->data['apellido_paterno'] . ' ' . $this->data['apellido_materno'],
                'given'                 => $this->data['nombre'],
                'fathers_family'        => $this->data['apellido_paterno'],
                'mothers_family'        => $this->data['apellido_materno'],
                'sex'                   => $sexValue,
                'gender'                => $sexGender,
                'birthday'              => date('Y-m-d', Carbon::createFromFormat('Y-m-d', $this->data['fecha_nacimiento'])->getTimestamp()),
                // 'cod_con_marital_id'    => $this->data['estado_civil'],
                'nationality_id'        => $nationality,
            ]
        );

        // GET RECORD
        $user_id = $userCreatedOrUpdated ? $userCreatedOrUpdated->id : null;
        $record = DependentUser::firstOrCreate(['user_id' => $user_id]);

        if ($user == null) {
            // SE CREA IDENTIFIER
            $identifierCreate = Identifier::create(
                [
                    'user_id'                       => $userCreatedOrUpdated->id,
                    'use'                           => 'official',
                    'cod_con_identifier_type_id'    => 1,
                    'value'                         => $this->data['run'],
                    'dv'                            => $this->data['dv']
                ]
            );

            //SE CREA HUMAN NAME
            $humanName = HumanName::create(
                [
                    'use'               => 'official',
                    'given'             => $this->data['nombre'],
                    'fathers_family'    => $this->data['apellido_paterno'],
                    'mothers_family'    => $this->data['apellido_materno'],
                    'period_start'      => now(),
                    'user_id'           => $userCreatedOrUpdated->id
                ]
            );
        }

        //ADDRESS
        $addressExist = new Address();
        foreach ($userCreatedOrUpdated->addresses as $address) {
            if ($address->use->value == 'home') {
                $addressExist = $address;
            }
        }

        $commune = Commune::where('name', $this->data['comuna'])->first()->id ?? null;

        $newAddress = Address::updateOrCreate(
            [
                'id'    => $addressExist ? $addressExist->id : null
            ],
            [

                'user_id'       => $userCreatedOrUpdated->id,
                'use'           => 'home',
                'type'          => 'physical',
                'text'          => $this->data['calle'],
                'line'          => $this->data['numero'],
                'apartment'     => $this->data['departamento'] ?? null,
                'suburb'        => null,
                'city'          => null,
                'commune_id'    => $commune,
                'postal_code'   => null,
                'region_id'     => null,
            ]
        );

        //LOCATION
        $street    = $this->data['calle'];
        $number     = $this->data['numero'];
        $commune    = $this->data['comuna'];

        if ($street && $number && $commune) {

            $geocodingService = app(GeocodingService::class);
            $coordinates = $geocodingService->getCoordinates($street . '+' . $number . '+' . $commune);

            if ($coordinates) {
                $latitude   = $coordinates['lat'];
                $longitude  = $coordinates['lng'];
            } else {
                $latitude   = null;
                $longitude  = null;
            }

            $newLocation = Location::updateOrCreate(
                [
                    'id'    => $newAddress->location ? $newAddress->location->id : null
                ],
                [
                    'address_id'        => $newAddress->id,
                    'longitude'         => $longitude,
                    'latitude'          => $latitude
                ]
            );
        }

        // Crear o Actualizar contactPoint
        $organization_id = preg_replace("/[^0-9]/", '', $this->data['establecimiento']);
        $organization_id = Organization::where('code_deis', '=', $organization_id)->first()->id ?? null;
        $contactPoint = ContactPoint::where('user_id', $userCreatedOrUpdated->id)->latest()->first();
        $contactPoint_upsert = ContactPoint::updateOrCreate(
            [
                'id'    => $contactPoint ? $contactPoint->id : null
            ],
            [
                'system'            => 'phone',
                'user_id'           => $userCreatedOrUpdated->id,
                'location_id'       => $newLocation->id ?? null,
                'value'             => $this->data['telefono'],
                'organization_id'   => $organization_id,
                'use'               => 'mobile',
                'actually'          => 0, // TODO: vaya agregando si cambia
            ]
        );



        /*
        *
        * Creator Importer cuidador
        *
        */

        if ($this->data['run_cuidador'] != '') {
            $user_caregiver = User::whereHas('identifiers', function ($query) {
                $query->where('value', $this->data['run_cuidador'])
                    ->Where('cod_con_identifier_type_id', 1);
            })
                ->first();

            $sexValue_caregiver = ClassSex::where('text', $this->data['sexo_cuidador'])->first()->value ?? null;
            $sexGender_caregiver = ClassGender::where('text', $this->data['genero_cuidador'])->first()->value ?? null;
            $nationality_caregiver = Country::where('name', $this->data['nacionalidad_cuidador'])->first()->id ?? null;

            $user_caregiver_upsert = User::updateOrCreate(
                [
                    'id'    => $user_caregiver ? $user_caregiver->id : null
                ],
                [
                    'active'                => 1,
                    'text'                  => $this->data['nombre_cuidador'] . ' ' . $this->data['apellido_paterno_cuidador'] . ' ' . $this->data['apellido_materno_cuidador'],
                    'given'                 => $this->data['nombre_cuidador'],
                    'fathers_family'        => $this->data['apellido_paterno_cuidador'],
                    'mothers_family'        => $this->data['apellido_materno_cuidador'],
                    'sex'                   => $sexValue_caregiver,
                    'gender'                => $sexGender_caregiver,
                    'birthday'              => date('Y-m-d', Carbon::createFromFormat('Y-m-d', $this->data['fecha_nacimiento_cuidador'])->getTimestamp()),
                    // 'cod_con_marital_id'    => $this->data['estado_civil'],
                    'nationality_id'        => $nationality_caregiver,
                ]
            );

            if ($user_caregiver == null) {
                // SE CREA IDENTIFIER
                $identifier_caregiver_create = Identifier::create(
                    [
                        'user_id'                       => $user_caregiver_upsert->id,
                        'use'                           => 'official',
                        'cod_con_identifier_type_id'    => 1,
                        'value'                         => $this->data['run_cuidador'],
                        'dv'                            => $this->data['dv_cuidador']
                    ]
                );

                //SE CREA HUMAN NAME
                $humanNameCaregiver = HumanName::create(
                    [
                        'use'               => 'official',
                        'given'             => $this->data['nombre_cuidador'],
                        'fathers_family'    => $this->data['apellido_paterno_cuidador'],
                        'mothers_family'    => $this->data['apellido_materno_cuidador'],
                        'period_start'      => now(),
                        'user_id'           => $user_caregiver_upsert->id
                    ]
                );
            }

            //ADDRESS
            $addressCaregiverExist = new Address();
            foreach ($user_caregiver_upsert->addresses as $address) {
                if ($address->use->value == 'home') {
                    $addressCaregiverExist = $address;
                }
            }

            $communeCaregiver = Commune::where('name', $this->data['comuna'])->first()->id;

            $newAddressCaregiver = Address::updateOrCreate(
                [
                    'id'    => $addressCaregiverExist ? $addressCaregiverExist->id : null
                ],
                [

                    'user_id'       => $user_caregiver_upsert->id,
                    'use'           => 'home',
                    'type'          => 'physical',
                    'text'          => $this->data['calle'],
                    'line'          => $this->data['numero'],
                    'apartment'     => $this->data['departamento'] ?? null,
                    'suburb'        => null,
                    'city'          => null,
                    'commune_id'    => $communeCaregiver,
                    'postal_code'   => null,
                    'region_id'     => null,
                ]
            );

            //LOCATION CAREGIVER
            $caregiverStreet    = $this->data['calle'];
            $caregiverNumber     = $this->data['numero'];
            $caregiverCommune    = $this->data['comuna'];
            if ($caregiverStreet && $caregiverNumber && $caregiverCommune) {

                $geocodingService = app(GeocodingService::class);
                $caregiverCordinates = $geocodingService->getCoordinates($caregiverStreet . '+' . $caregiverNumber . '+' . $caregiverCommune);

                if ($coordinates) {
                    $caregiverLatitude   = $caregiverCordinates['lat'];
                    $caregiverLongitude  = $caregiverCordinates['lng'];
                } else {
                    $caregiverLatitude   = null;
                    $caregiverLongitude  = null;
                }

                $newCaregiverLocation = Location::updateOrCreate(
                    [
                        'id'    => $newAddressCaregiver->location ? $newAddressCaregiver->location->id : null
                    ],
                    [
                        'address_id'        => $newAddressCaregiver->id,
                        'longitude'         => $longitude,
                        'latitude'          => $latitude
                    ]
                );
            }

            // Verificar que no exista ya un caregiver y si existe actualizar
            $caregiver = DependentCaregiver::whereHas(
                'user',
                function ($query) use ($user_caregiver_upsert) {
                    $query->where('id', $user_caregiver_upsert->id);
                }
            )->first();

            $caregiver_upsert = DependentCaregiver::updateOrCreate(
                [
                    'id'    => $caregiver ? $caregiver->id : null
                ],
                [
                    'dependent_user_id'     => $record->id,
                    'user_id'               => $user_caregiver_upsert->id,
                    'relative'              => $this->data['parentesco_cuidador'],
                    'healthcare_type'       => $this->validateHealthcareType($this->data['prevision_cuidador']),
                    'empam'                 => $this->validateBool($this->data['empam_cuidador']),
                    'zarit'                 => $this->validateBool($this->data['zarit_cuidador']),
                    'immunizations'         => $this->data['inmunizaciones_cuidador'],
                    'elaborated_plan'       => $this->validateBool($this->data['plan_elaborado_cuidador']),
                    'evaluated_plan'        => $this->validateBool($this->data['plan_evaluado_cuidador']),
                    'trained'               => $this->validateBool($this->data['capacitacion_cuidador']),
                    'stipend'               => $this->validateBool($this->data['estipendio_cuidador']),
                ]
            );


            // Crear o Actualizar contactPoint del cuidador
            $caregiverContactPoint = ContactPoint::where('user_id', $user_caregiver_upsert->id)->latest()->first();
            $caregiverContactPoint_upsert = ContactPoint::updateOrCreate(
                [
                    'id'    => $caregiverContactPoint ? $caregiverContactPoint->id : null
                ],
                [
                    'system'            => 'phone',
                    'user_id'           => $user_caregiver_upsert->id,
                    'location_id'       => $newCaregiverLocation->id ?? null,
                    'value'             => $this->data['telefono'],
                    'organization_id'   => $organization_id,
                    'use'               => 'mobile',
                    'actually'          => 0, // TODO: vaya agregando si cambia
                ]
            );
        }

        // $dependent_user_id = $user->dependentUser->id ?? null;
        // $record = DependentUser::find($dependent_user_id) ?? new DependentUser;




        $record->user_id  = $userCreatedOrUpdated->id;
        $record->diagnosis = $this->data['diagnostico'];
        $record->healthcare_type = $this->validateHealthcareType($this->data['prevision']);
        $record->check_in_date = $this->validateDate($this->data['fecha_ingreso']);
        $record->check_out_date = $this->validateDate($this->data['fecha_egreso']);
        $record->integral_visits = $this->validateInt($this->data['visitas_integrales']);
        $record->treatment_visits = $this->validateInt($this->data['visitas_tratamiento']);
        $record->last_integral_visit = $this->validateDate($this->data['fecha_visita_integral']);
        $record->last_treatment_visit =  $this->validateDate($this->data['fecha_visita_tratamiento']);
        $record->barthel = $this->validateBarthel($this->data['barthel']);
        $record->empam = $this->validateBool($this->data['emp_empam']);
        $record->eleam = $this->validateBool($this->data['eleam']);
        $record->upp = $this->validateBool($this->data['upp']);
        $record->elaborated_plan = $this->validateBool($this->data['plan_elaborado']);
        $record->evaluated_plan = $this->validateBool($this->data['plan_evaluado']);
        $record->diapers_size = $this->data['talla_panal'];
        $record->pneumonia = $this->validateDate($this->data['neumo']);
        $record->influenza = $this->validateDate($this->data['influenza']);
        $record->covid_19 = $this->validateDate($this->data['covid_19']);
        $record->extra_info = $this->data['extra_info'];
        $record->tech_aid = $this->validateBool($this->data['ayuda_tecnica']);
        $record->tech_aid_date = $this->validateDate($this->data['ayuda_tecnica_fecha']);
        $record->nutrition_assistance = $this->validateBool($this->data['entrega_alimentacion']);
        $record->nutrition_assistance_date = $this->validateDate($this->data['entrega_alimentacion_fecha']);
        $record->nasogastric_catheter = $this->validateInt(intval(trim($this->data['sonda_sng'])));
        $record->urinary_catheter = $this->validateInt(intval(trim($this->data['sonda_urinaria'])));
        $record->save();

        return $record;
    }

    public function validateInt($val)
    {
        $out = null;
        $clean = intval(trim($val));
        if ($clean != 0) {
            $out = strval($clean);
        }
        return $out;
    }

    public function validateBool($text)
    {
        $out = null;
        $text = strtolower(trim($text));
        if ($text == 'si' || $text == 'ok') {
            $out = true;
        } else if ($text == 'no' || $text == 'p') {
            $out = false;
        }
        return $out;
    }

    public function validateDate($text)
    {
        $out = null;
        if ($text != '') {
            $date_str = DateTime::createFromFormat('d/m/Y', $text);
            if ($date_str != false) {
                $out = $date_str->format('Y-m-d');
            }
        }
        return $out;
    }

    public function validateBarthel($text)
    {
        $out = null;
        $text = strtolower(trim($text));
        switch ($text) {
            case 'independiente':
                $out = 'independent';
                break;
            case 'leve':
                $out = 'slight';
                break;
            case 'moderado':
                $out = 'moderate';
                break;
            case 'grave':
                $out = 'severe';
                break;
            case 'total':
                $out = 'total';
                break;
        }
        return $out;
    }

    public function validateHealthcareType($text)
    {
        $out = null;
        $words = explode(' ', $text);
        $text = (count($words) > 1) ? array_pop($words) : $text;
        $text = strtolower(trim($text));
        switch ($text) {
            case 'a':
                $out = 'FONASA A';
                break;
            case 'b':
                $out = 'FONASA B';
                break;
            case 'c':
                $out = 'FONASA C';
                break;
            case 'd':
                $out = 'FONASA D';
                break;
            case 'isapre':
                $out = 'ISAPRE';
                break;
            case 'prais':
                $out = 'PRAIS';
                break;
        }
        return $out;
    }
}
