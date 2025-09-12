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
use App\Models\Condition;
use App\Models\ContactPoint;
use App\Models\Location;
use App\Models\Sex;
use App\Models\Gender;
use App\Models\Country;
use App\Models\Organization;
use DateTime;
use Carbon\Carbon;

class CreateDependentUser extends CreateRecord
{
    protected static string $resource = DependentUserResource::class;

    private $date_format = 'Y-m-d';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Datos del Paciente')
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
                            ->options(Country::pluck('name', 'id'))
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('comuna')
                            ->label('Comuna')
                            ->options(Commune::pluck('name', 'id'))
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
                        Forms\Components\Select::make('establecimiento')
                            ->label('Establecimiento')

                            ->options(Organization::whereService(3)->whereNotNull('code_deis')->pluck('alias', 'code_deis'))
                            ->columnSpan(1),
                        Forms\Components\Select::make('prevision')
                            ->label('Previsión')
                            ->options([
                                'FONASA A'  => 'Fonasa A',
                                'FONASA B'  => 'Fonasa B',
                                'FONASA C'  => 'Fonasa C',
                                'FONASA D'  => 'Fonasa D',
                                'ISAPRE'    => 'Isapre',
                                'PRAIS'     => 'PRAIS'
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
                        Forms\Components\DatePicker::make('fecha_visita_integral')
                            ->label('Fecha de Visita Integral')
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('fecha_visita_tratamiento')
                            ->label('Fecha de visita de Tratamiento')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('visitas_integrales')
                            ->label('Visitas Integrales')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('visitas_tratamiento')
                            ->label('Visitas Tratamiento')
                            ->columnSpan(1),
                        Forms\Components\Select::make('barthel')
                            ->label('BARTHEL')
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
                    ])->columns(2),
                Forms\Components\Section::make('Condiciones del Paciente')
                    ->schema([
                        Forms\Components\Select::make('condiciones')
                            ->label('Condiciones')
                            ->required()
                            ->options(Condition::pluck('name', 'id'))
                            ->multiple()
                            ->columnSpan(2),
                    ])->columns(2),
                Forms\Components\Section::make('Datos del Cuidador')
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
                                'male' => 'Masculino',
                                'female' => 'Femenino',
                                'other' => 'Otro',
                                'unknown' => 'Desconocido',
                            ])
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('genero_cuidador')
                            ->label('Género Cuidador')
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
                        Forms\Components\Select::make('nacionalidad_cuidador')
                            ->label('Nacionalidad Cuidador')
                            ->options(Country::pluck('name', 'id'))
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('parentesco_cuidador')
                            ->label('Parentesco Cuidador')
                            ->options([
                                'CONYUGE' => 'Conyuge',
                                'PADRE' => 'Padre o Madre',
                                'HIJO' => 'Hijo/a',
                                'PAGADO' => 'Cuidador Pagado',
                                'OTRO' => 'Otro',
                            ])
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('prevision_cuidador')
                            ->label('Previsión Cuidador')
                            ->options([
                                'FONASA A'  => 'Fonasa A',
                                'FONASA B'  => 'Fonasa B',
                                'FONASA C'  => 'Fonasa C',
                                'FONASA D'  => 'Fonasa D',
                                'ISAPRE'    => 'Isapre',
                                'PRAIS'     => 'PRAIS'
                            ])
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('empam_cuidador')
                            ->label('EMPAM Cuidador')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('zarit_cuidador')
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
                    ])->columns(2),
            ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        /*  $rules = [
            'data.nombre'                       => 'required|string',
            'data.apellido_paterno'             => 'required|string',
            'data.apellido_materno'             => 'required|string',
            'data.run'                          => 'required|numeric',
            'data.dv'                           => 'required|numeric',
            'data.fecha_nacimiento'             => 'required|date',
            'data.sexo'                         => 'required|string',
            'data.genero'                       => 'nullable|string',
            'data.nacionalidad'                 => 'required|string',
            'data.comuna'                       => 'required|string',
            'data.calle'                        => 'required|string',
            'data.numero'                       => 'required|string',
            'data.departamento'                 => 'nullable|string',
            'data.telefono'                     => 'required|numeric',
            'data.establecimiento'              => 'tring',
            'data.prevision'                    => 'required|string',
            'data.diagnostico'                  => 'required|string',
            'data.fecha_ingreso'                => 'nullable|date',
            'data.fecha_egreso'                 => 'nullable|date',
            'data.visitas_integrales'           => 'nullable|numeric',
            'data.visitas_tratamiento'          => 'nullable|numeric',
            'data.barthel'                      => 'nullable|string',
            'data.emp_empam'                    => 'nullable|boolean',
            'data.eleam'                        => 'nullable|boolean',
            'data.upp'                          => 'nullable|boolean',
            'data.plan_elaborado'               => 'nullable|boolean',
            'data.plan_evaluado'                => 'nullable|boolean',
            'data.neumo'                        => 'nullable|date',
            'data.influenza'                    => 'nullable|date',
            'data.covid_19'                     => 'nullable|date',
            'data.ayuda_tecnica'                => 'nullable|boolean',
            'data.ayuda_tecnica_fecha'          => 'nullable|date',
            'data.entrega_alimentacion'         => 'nullable|boolean',
            'data.entrega_alimentacion_fecha'   => 'nullable|date',
            'data.talla_panal'                  => 'nullable|numeric',
            'data.condiciones'                  => 'required|array',
            'data.sonda_sng'                    => 'nullable|numeric',
            'data.sonda_urinaria'               => 'nullable|numeric',
            'data.extra_info'                   => 'nullable|string',
            'data.nombre_cuidador'              => 'required|string', // Requiered if in form has_cuidador is true
            'data.apellido_paterno_cuidador'    => 'required|string', // Requiered if in form has_cuidador is true
            'data.apellido_materno_cuidador'    => 'required|string', // Requiered if in form has_cuidador is true
            'data.fecha_nacimiento_cuidador'    => 'required|date', // Requiered if in form has_cuidador is true
            'data.run_cuidador'                 => 'required|numeric', // Requiered if in form has_cuidador is true
            'data.dv_cuidador'                  => 'required|numeric', // Requiered if in form has_cuidador is true
            'data.sexo_cuidador'                => 'required|string', // Requiered if in form has_cuidador is true
            'data.genero_cuidador'              => 'nullable|string',
            'data.nacionalidad_cuidador'        => 'required|array', // Requiered if in form has_cuidador is true
            'data.parentesco_cuidador'          => 'required|string', // Requiered if in form has_cuidador is true
            'data.prevision_cuidador'           => 'nullable|string',
            'data.empam_cuidador'               => 'nullable|boolean',
            'data.zarit_cuidador'               => 'nullable|boolean',
            'data.inmunizaciones_cuidador'      => 'nullable|date',
            'data.plan_elaborado_cuidador'      => 'nullable|boolean',
            'data.plan_evaluado_cuidador'       => 'nullable|boolean',
            'data.capacitacion_cuidador'        => 'nullable|boolean',
            'data.estipendio_cuidador'          => 'nullable|boolean',
        ]; */

        $input = [];
        foreach ($data as $name => $value) {
            $input[$name] = $value ?? null;
        }
        // Upsert an User, Address, ContactPoint, for Upsert a DependentUser and Attach Conditions
        $dependentUser = $this->getDependentUser($data);

        // Upsert an User, Address, ContactPoint, for Upsert a DependentCaregiver        
        $this->getCaregiver($data, $dependentUser);

        return $dependentUser;
    }



    public function getUser($row, $cuidador = false)
    {
        $cuidador = $cuidador ? '_cuidador' : '';
        $run = $row['run' . $cuidador];
        $dv = $row['dv' . $cuidador];
        $sexo = $row['sexo' . $cuidador];
        $genero = $row['genero' . $cuidador];
        $nacionalidad = $row['nacionalidad' . $cuidador];
        $nombre = $row['nombre' . $cuidador];
        $apellido_paterno = $row['apellido_paterno' . $cuidador];
        $apellido_materno = $row['apellido_materno' . $cuidador];
        $fecha_nacimiento = $this->formatField($row['fecha_nacimiento' . $cuidador], 'date');

        // Check if user exists
        $user = User::whereHas('identifiers', function ($query) use ($run) {
            $query->where('value', $run)
                ->where('cod_con_identifier_type_id', 1);
        })->first();

        // Obtain possible values
        $sex = Sex::where('text', $sexo)->first()?->value;
        $gender = Gender::where('text', $genero)->first()?->value;
        $nationality = Country::where('name', $nacionalidad)->first()?->id;

        // If the user does not exist, create a new one
        $userOut = User::updateOrCreate(
            [
                'id'    => $user ? $user->id : null
            ],
            [
                'active'                => 1,
                'text'                  => $nombre . ' ' . $apellido_paterno . ' ' . $apellido_materno,
                'given'                 => $nombre,
                'fathers_family'        => $apellido_paterno,
                'mothers_family'        => $apellido_materno,
                'sex'                   => $sex,
                'gender'                => $gender,
                'birthday'              => $fecha_nacimiento ? Carbon::parse($fecha_nacimiento)->format('d-m-Y') : null,
                // 'cod_con_marital_id'    => $row['estado_civil'],
                'nationality_id'        => $nationality,
            ]
        );

        if ($user == null) {
            // SE CREA IDENTIFIER
            Identifier::create(
                [
                    'user_id'                       => $userOut->id,
                    'use'                           => 'official',
                    'cod_con_identifier_type_id'    => 1,
                    'value'                         => $run,
                    'dv'                            => $dv
                ]
            );

            //SE CREA HUMAN NAME
            HumanName::create(
                [
                    'use'               => 'official',
                    'given'             => $nombre,
                    'fathers_family'    => $apellido_paterno,
                    'mothers_family'    => $apellido_materno,
                    'period_start'      => now(),
                    'user_id'           => $userOut->id
                ]
            );
        }
        $this->getAddress($row, $userOut);
        return $userOut;
    }

    public function getAddress($row, $user)
    {

        $calle = $row['calle'];
        $numero = $row['numero'];
        $departamento = $row['departamento'];
        $comuna = $row['comuna'];

        // Check if user has home address
        $addressExist = null;
        foreach ($user->addresses as $address) {
            if ($address->use == 'home') {
                $addressExist = $address;
            }
        }

        // Get commune id from name
        $commune = Commune::where('name', $comuna)->first()?->id;

        // Create or update address
        $address = Address::updateOrCreate(
            [
                'id' => $addressExist ? $addressExist->id : null
            ],
            [
                'user_id'       => $user->id,
                'use'           => 'home',
                'type'          => 'physical',
                'text'          => $calle,
                'line'          => $numero,
                'apartment'     => $departamento,
                'suburb'        => null,
                'city'          => null,
                'commune_id'    => $commune,
                'postal_code'   => null,
                'region_id'     => null,
            ]
        );

        // Get coordinates and create location
        if ($calle && $numero && $comuna) {
            $geocodingService = app(GeocodingService::class);
            $coordinates = $geocodingService->getCoordinates($calle . '+' . $numero . '+' . $comuna);

            $latitude = $coordinates['lat'] ?? null;
            $longitude = $coordinates['lng'] ?? null;

            Location::updateOrCreate(
                [
                    'id' => $address->location?->id ?? null
                ],
                [
                    'address_id' => $address->id,
                    'longitude'  => $longitude,
                    'latitude'   => $latitude
                ]
            );
        }

        $this->getContactPoint($row, $user, $address);

        return $address;
    }

    public function getContactPoint($row, $user, $address = null)
    {
        $telefono = $row['telefono'];
        $establecimiento = $row['establecimiento'];

        // Get organization_id from code_deis
        $organization_id = preg_replace("/[^0-9]/", '', $establecimiento);
        $organization_id = Organization::where('code_deis', '=', $organization_id)->first()?->id;

        // Check if user has a contact point
        $contactPoint = ContactPoint::where('user_id', $user->id)->latest()->first();

        // Create or update contact point
        return ContactPoint::updateOrCreate(
            [
                'id' => $contactPoint ? $contactPoint->id : null
            ],
            [
                'system'            => 'phone',
                'user_id'           => $user->id,
                'location_id'       => $address?->location?->id,
                'value'             => $telefono,
                'organization_id'   => $organization_id,
                'use'              => 'mobile',
                'actually'         => 0
            ]
        );
    }

    public function getDependentUser($row)
    {
        $user = $this->getUser($row);

        // Create or update DependentUser
        $dependentUser = DependentUser::updateOrCreate(
            [
                'user_id' => $user->id
            ],
            [
                'diagnosis' => $this->formatField($row['diagnostico'], 'text'),
                'healthcare_type' => $this->formatField($row['prevision'], 'healthcare'),
                'check_in_date' => $this->formatField($row['fecha_ingreso'], 'date'),
                'check_out_date' => $this->formatField($row['fecha_egreso'], 'date'),
                'integral_visits' => $this->formatField($row['visitas_integrales'], 'integer'),
                'treatment_visits' => $this->formatField($row['visitas_tratamiento'], 'integer'),
                'last_integral_visit' => $this->formatField($row['fecha_visita_integral'], 'date'),
                'last_treatment_visit' => $this->formatField($row['fecha_visita_tratamiento'], 'date'),
                'barthel' => $this->formatField($row['barthel'], 'barthel'),
                'empam' => $this->formatField($row['emp_empam'], 'boolean'),
                'eleam' => $this->formatField($row['eleam'], 'boolean'),
                'upp' => $this->formatField($row['upp'], 'boolean'),
                'elaborated_plan' => $this->formatField($row['plan_elaborado'], 'boolean'),
                'evaluated_plan' => $this->formatField($row['plan_evaluado'], 'boolean'),
                'diapers_size' => $row['talla_panal'],
                'pneumonia' => $this->formatField($row['neumo'], 'date'),
                'influenza' => $this->formatField($row['influenza'], 'date'),
                'covid_19' => $this->formatField($row['covid_19'], 'date'),
                'extra_info' => $row['extra_info'],
                'tech_aid' => $this->formatField($row['ayuda_tecnica'], 'boolean'),
                'tech_aid_date' => $this->formatField($row['ayuda_tecnica_fecha'], 'date'),
                'nutrition_assistance' => $this->formatField($row['entrega_alimentacion'], 'boolean'),
                'nutrition_assistance_date' => $this->formatField($row['entrega_alimentacion_fecha'], 'date'),
                'nasogastric_catheter' => $this->formatField($row['sonda_sng'], 'integer'),
                'urinary_catheter' => $this->formatField($row['sonda_urinaria'], 'integer')
            ]
        );

        $attachIds = array_keys($row['condiciones']);
        if (!empty($attachIds)) {
            $dependentUser->conditions()->syncWithoutDetaching($attachIds);
        }
        return $dependentUser;
    }

    public function getCaregiver($row, $dependentUser)
    {
        $caregiverUser = $this->getUser($row, true);
        // Check if caregiver exists
        $caregiver = DependentCaregiver::whereHas('user', function ($query) use ($caregiverUser) {
            $query->where('id', $caregiverUser->id);
        })->first();

        // Create or update caregiver
        $dependentCaregiver = DependentCaregiver::updateOrCreate(
            [
                'id' => $caregiver ? $caregiver->id : null
            ],
            [
                'dependent_user_id' => $dependentUser->id,
                'user_id'          => $caregiverUser->id,
                'relative'         => $row['parentesco_cuidador'],
                'healthcare_type'  => $this->formatField($row['prevision_cuidador'], 'healthcare'),
                'empam'           => $this->formatField($row['empam_cuidador'], 'boolean'),
                'zarit'           => $this->formatField($row['zarit_cuidador'], 'boolean'),
                'immunizations'    => $row['inmunizaciones_cuidador'],
                'elaborated_plan' => $this->formatField($row['plan_elaborado_cuidador'], 'boolean'),
                'evaluated_plan'  => $this->formatField($row['plan_evaluado_cuidador'], 'boolean'),
                'trained'         => $this->formatField($row['capacitacion_cuidador'], 'boolean'),
                'stipend'         => $this->formatField($row['estipendio_cuidador'], 'boolean')
            ]
        );
        return $dependentCaregiver;
    }

    public function formatField($value, $type)
    {
        switch ($type) {
            case 'boolean':
                $value = strtolower(trim($value));
                return $value == 'si' || $value == 'ok' ? true : ($value == 'no' || $value == 'p' ? false : null);

            case 'integer':
                $clean = intval(trim($value));
                return $clean != 0 ? strval($clean) : null;

            case 'date':
                if (is_null($value)) {
                    return null;
                }
                $value = intval(trim($value));
                // $date = DateTime::createFromFormat('d/m/Y', $value)->format($this->date_format);
                $date = DateTime::createFromFormat($this->date_format, $value);

                return $date ?? null;

            case 'barthel':
                $map = [
                    'independiente' => 'independent',
                    'leve' => 'slight',
                    'moderado' => 'moderate',
                    'grave' => 'severe',
                    'total' => 'total'
                ];
                return $map[strtolower(trim($value))] ?? null;

            case 'healthcare':
                $value = strtolower(trim($value));
                $words = explode(' ', $value);
                $value = (count($words) > 1) ? array_pop($words) : $value;
                $map = [
                    'a' => 'FONASA A',
                    'b' => 'FONASA B',
                    'c' => 'FONASA C',
                    'd' => 'FONASA D',
                    'isapre' => 'ISAPRE',
                    'prais' => 'PRAIS'
                ];
                return $map[$value] ?? null;
            default:
                return $value;
        }
    }
}
