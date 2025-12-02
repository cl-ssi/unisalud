<?php

namespace App\Imports;

use App\Models\Address;
use App\Models\Commune;
use App\Models\Condition;
use App\Models\ContactPoint;
use App\Models\Country;
use App\Models\DependentCaregiver;
use App\Models\DependentUser;
use App\Models\Gender;
use App\Models\HumanName;
use App\Models\Identifier;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Sex;
use App\Models\User;
use App\Services\GeocodingService;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Notifications\Notification;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue; // Para la cola
use Maatwebsite\Excel\Concerns\WithChunkReading; // Para leer en trozos
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

// use Maatwebsite\Excel\Concerns\WithValidation;


class DependentUserImport implements ToModel, WithHeadingRow, WithChunkReading, WithEvents // , ShouldQueue, WithValidation
{
    private $date_format = 'Y-m-d';

    protected static bool $rowNew = false;
    protected static int $insertedCount = 0;
    protected static int $updatedCount = 0;
    protected static int $skippedCount = 0;




    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        self::$rowNew = false;
        $headings = [
            'establecimiento',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'run',
            'dv',
            'prevision',
            'sexo',
            'genero',
            'fecha_nacimiento',
            'nacionalidad',
            'diagnostico',
            'calle',
            'numero',
            'departamento',
            'comuna',
            'telefono',
            'fecha_ingreso',
            'fecha_egreso',
            'visitas_integrales',
            'fecha_visita_integral',
            'visitas_tratamiento',
            'fecha_visita_tratamiento',
            'barthel',
            'emp_empam',
            'eleam',
            'upp',
            'plan_elaborado',
            'plan_evaluado',
            'neumo',
            'influenza',
            'covid_19',
            'extra_info',
            'ayuda_tecnica',
            'ayuda_tecnica_fecha',
            'entrega_alimentacion',
            'entrega_alimentacion_fecha',
            'talla_panal',
            'sonda_sng',
            'sonda_urinaria',
            'nombre_cuidador',
            'apellido_paterno_cuidador',
            'apellido_materno_cuidador',
            'fecha_nacimiento_cuidador',
            'run_cuidador',
            'dv_cuidador',
            'sexo_cuidador',
            'genero_cuidador',
            'nacionalidad_cuidador',
            'parentesco_cuidador',
            'prevision_cuidador',
            'empam_cuidador',
            'zarit_cuidador',
            'inmunizaciones_cuidador',
            'plan_elaborado_cuidador',
            'plan_evaluado_cuidador',
            'capacitacion_cuidador',
            'estipendio_cuidador'
        ];
        // $row = array_change_key_case($row, CASE_LOWER);
        foreach ($headings as $heading) {
            $row[$heading] = $row[$heading] ?? null;
        }

        if (isset($row['run']) && isset($row['dv'])) {

            // Upsert an User, Address, ContactPoint, for Upsert a DependentUser and Attach Conditions
            $dependentUser = $this->getDependentUser($row);
            if (isset($row['run_cuidador']) && isset($row['dv_cuidador'])) {
                // Upsert an User, Address, ContactPoint, for Upsert a DependentCaregiver        
                $this->getCaregiver($row, $dependentUser);
            }
            self::$insertedCount++;
            return $dependentUser;
        } else {
            self::$skippedCount++;
            return null;
        }
    }

    public static function getCompletedNotificationBody(): string
    {
        $body = 'La importación de usuarios dependientes ha finalizado. ';
        $body .= self::$insertedCount . ' ' . str('fila')->plural(self::$insertedCount) . ' insertada(s), ';
        $body .= self::$updatedCount . ' ' . str('fila')->plural(self::$updatedCount) . ' actualizada(s) y ';
        $body .= self::$skippedCount . ' ' . str('fila')->plural(self::$skippedCount) . ' omitida(s).';

        // Es crucial reiniciar los contadores para la próxima importación que se ejecute.
        self::$insertedCount = 0;
        self::$updatedCount = 0;
        self::$skippedCount = 0;
        Log::info($body);
        return $body;
    }

    /*
    public function rules(): array
    {
        return [
            // // Datos para model User del DependentUser
            // 'nombre' => 'required',
            // 'apellido_paterno' => 'required',
            // 'apellido_materno' => 'required', 
            // 'run' => 'required',
            // 'prevision' => 'required',
            // 'sexo' => 'required',

            // // Datos para la tabla Conditions

            // 'movilidad_reducida' => ['nullable', Rule::in(['SI', 'NO'])],
            // 'oxigeno_dependiente' => ['required', Rule::in(['SI', 'NO'])],
            // 'alimentacion_enteral' => ['required', Rule::in(['SI', 'NO'])],
            // 'demencia' => ['required', Rule::in(['SI', 'NO'])],
            // 'oncologicos' => ['required', Rule::in(['SI', 'NO'])],
            // 'cuidados_paliativos_universales' => ['required', Rule::in(['SI', 'NO'])],
            // 'naneas' => ['required', Rule::in(['SI', 'NO'])]
        ];
    }
    */

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
        $fecha_nacimiento = $row['fecha_nacimiento' . $cuidador];

        // $fecha_nacimiento = $fecha_nacimiento?date('Y-m-d', Carbon::createFromFormat('d/m/Y', $fecha_nacimiento)->getTimestamp()):null;
        // $fecha_nacimiento = $fecha_nacimiento ? Date::excelToDateTimeObject($fecha_nacimiento)->format($this->date_format) : null;


        // Check if user exists
        $user = User::whereHas('identifiers', function ($query) use ($run) {
            $query->where('value', $run)
                ->where('cod_con_identifier_type_id', 1);
        })->first();

        self::$skippedCount = $user?->id ? true : false;

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
                'birthday'              => $this->formatField($fecha_nacimiento, 'date'),
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
        if (isset($row['calle'])) {
            $this->getAddress($row, $userOut);
        }
        return $userOut;
    }

    public function getAddress($row, $user)
    {

        $calle = $row['calle'];
        $numero = $row['numero'];
        $departamento = $row['departamento'];
        $comuna = ucfirst($row['comuna']);

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
                'commune_id'    => $commune->id ?? null,
                'postal_code'   => null,
                'region_id'     => $commune->region_id ?? null,
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
        if (Self::$rowNew) {
            self::$insertedCount++;
        } else {
            self::$updatedCount++;
        }
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

        // Attach conditions optimally
        $conditions = Condition::parentsOnly()->pluck('id', 'name')->all();
        $childs = Condition::childsOnly()->pluck('id', 'code')->all();
        $electro = strtoupper($row['electrodependencia'] ?? '');
        $attachIds = [];

        foreach ($conditions as $name => $id) {
            $val = $this->formatField($row[str_replace(" ", "_", $name)] ?? null, 'boolean');
            if ($val === true) {
                $attachIds[] = $id;
            } elseif ($name === 'electrodependencia' && array_key_exists($electro, $childs) && $val != false) {
                $attachIds[] = $id;
                $attachIds[] = $childs[$electro];
            }
        }
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
                $date = Date::excelToDateTimeObject($value)->format($this->date_format);

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

    public function chunkSize(): int
    {
        return 10;
    }

    public function registerEvents(): array
    {
        return [
            // Define your event listeners here
        ];
    }

    public static function afterImport(AfterImport $event)
    {
        Notification::make('importado')
            ->title('Archivo Importado')
            ->success()
            ->body(self::getCompletedNotificationBody());
    }
}
