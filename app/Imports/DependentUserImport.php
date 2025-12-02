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

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Filament\Notifications\Notification;

class DependentUserImport implements ToModel, WithHeadingRow, WithChunkReading, WithEvents, ShouldQueue
{
    private $date_format = 'Y-m-d';

    // Cachés para evitar queries repetidas
    private static $sexCache = null;
    private static $genderCache = null;
    private static $countriesCache = null;
    private static $communesCache = null;
    private static $organizationsCache = null;
    private static $conditionsParents = null;
    private static $conditionsChilds = null;

    protected static int $insertedCount = 0;
    protected static int $updatedCount = 0;
    protected static int $skippedCount = 0;

    public function __construct()
    {
        Log::info('=== DependentUserImport CONSTRUCTOR ===');

        // Cargar todos los datos una sola vez
        if (self::$sexCache === null) {
            self::$sexCache = Sex::pluck('value', 'text')->toArray();
            self::$genderCache = Gender::pluck('value', 'text')->toArray();
            self::$countriesCache = Country::pluck('id', 'name')->toArray();
            self::$communesCache = Commune::with('region')->get()->keyBy('name');
            self::$organizationsCache = Organization::pluck('id', 'code_deis')->toArray();
            self::$conditionsParents = Condition::parentsOnly()->pluck('id', 'name')->toArray();
            self::$conditionsChilds = Condition::childsOnly()->pluck('id', 'code')->toArray();

            Log::info('Cachés cargados', [
                'sexos' => count(self::$sexCache),
                'generos' => count(self::$genderCache),
                'paises' => count(self::$countriesCache),
                'comunas' => count(self::$communesCache),
                'organizaciones' => count(self::$organizationsCache)
            ]);
        }
    }

    public function model(array $row)
    {
        Log::info('=== PROCESANDO FILA ===', ['run' => $row['run'] ?? 'sin run']);

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
            'estipendio_cuidador',
            'electrodependencia'
        ];

        foreach ($headings as $heading) {
            $row[$heading] = $row[$heading] ?? null;
        }

        if (empty($row['run']) || empty($row['dv'])) {
            self::$skippedCount++;
            Log::warning('Fila sin RUN, saltando');
            return null;
        }

        try {
            $dependentUser = $this->getDependentUser($row);

            if (!empty($row['run_cuidador']) && !empty($row['dv_cuidador'])) {
                $this->getCaregiver($row, $dependentUser);
            }

            Log::info('Fila procesada exitosamente', ['user_id' => $dependentUser->user_id]);
            return $dependentUser;
        } catch (\Exception $e) {
            self::$skippedCount++;
            Log::error('Error procesando fila', [
                'run' => $row['run'],
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return null;
        }
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
        $fecha_nacimiento = $row['fecha_nacimiento' . $cuidador];

        // Usar caché en lugar de queries
        $sex = self::$sexCache[$sexo] ?? null;
        $gender = self::$genderCache[$genero] ?? null;
        $nationality = self::$countriesCache[$nacionalidad] ?? null;

        // Check if user exists
        $user = User::whereHas('identifiers', function ($query) use ($run) {
            $query->where('value', $run)->where('cod_con_identifier_type_id', 1);
        })->first();

        $isNew = !$user;

        if ($isNew) {
            self::$insertedCount++;
        } else {
            self::$updatedCount++;
        }

        // Create or update user
        $userOut = User::updateOrCreate(
            ['id' => $user?->id],
            [
                'active' => 1,
                'text' => trim($nombre . ' ' . $apellido_paterno . ' ' . $apellido_materno),
                'given' => $nombre,
                'fathers_family' => $apellido_paterno,
                'mothers_family' => $apellido_materno,
                'sex' => $sex,
                'gender' => $gender,
                'birthday' => $this->formatField($fecha_nacimiento, 'date'),
                'nationality_id' => $nationality,
            ]
        );

        if ($isNew) {
            Identifier::create([
                'user_id' => $userOut->id,
                'use' => 'official',
                'cod_con_identifier_type_id' => 1,
                'value' => $run,
                'dv' => $dv
            ]);

            HumanName::create([
                'use' => 'official',
                'given' => $nombre,
                'fathers_family' => $apellido_paterno,
                'mothers_family' => $apellido_materno,
                'period_start' => now(),
                'user_id' => $userOut->id
            ]);
        }

        if (!empty($row['calle']) && !$cuidador) {
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

        $addressExist = $user->addresses()->where('use', 'home')->first();

        // Usar caché
        $communeData = self::$communesCache[$comuna] ?? null;

        $address = Address::updateOrCreate(
            ['id' => $addressExist?->id],
            [
                'user_id' => $user->id,
                'use' => 'home',
                'type' => 'physical',
                'text' => $calle,
                'line' => $numero,
                'apartment' => $departamento,
                'commune_id' => $communeData?->id,
                'region_id' => $communeData?->region_id,
            ]
        );

        // Geocoding solo si hay datos completos
        if ($calle && $numero && $comuna && !$address->location) {
            try {
                $geocodingService = app(GeocodingService::class);
                $coordinates = $geocodingService->getCoordinates($calle . '+' . $numero . '+' . $comuna);

                if (!empty($coordinates['lat']) && !empty($coordinates['lng'])) {
                    Location::create([
                        'address_id' => $address->id,
                        'longitude' => $coordinates['lng'],
                        'latitude' => $coordinates['lat']
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Error obteniendo coordenadas', ['address' => $address->id]);
            }
        }

        $this->getContactPoint($row, $user, $address);
        return $address;
    }

    public function getContactPoint($row, $user, $address = null)
    {
        $telefono = $row['telefono'];
        $establecimiento = $row['establecimiento'];

        $organization_id = preg_replace("/[^0-9]/", '', $establecimiento);
        $organization_id = self::$organizationsCache[$organization_id] ?? null;

        $contactPoint = $user->contactPoints()->latest()->first();

        return ContactPoint::updateOrCreate(
            ['id' => $contactPoint?->id],
            [
                'system' => 'phone',
                'user_id' => $user->id,
                'location_id' => $address?->location?->id,
                'value' => $telefono,
                'organization_id' => $organization_id,
                'use' => 'mobile',
                'actually' => 0
            ]
        );
    }

    public function getDependentUser($row)
    {
        $user = $this->getUser($row);

        $dependentUser = DependentUser::updateOrCreate(
            ['user_id' => $user->id],
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

        // Attach conditions usando caché
        $electro = strtoupper($row['electrodependencia'] ?? '');
        $attachIds = [];

        foreach (self::$conditionsParents as $name => $id) {
            $val = $this->formatField($row[str_replace(" ", "_", strtolower($name))] ?? null, 'boolean');
            if ($val === true) {
                $attachIds[] = $id;
            } elseif ($name === 'electrodependencia' && array_key_exists($electro, self::$conditionsChilds) && $val != false) {
                $attachIds[] = $id;
                $attachIds[] = self::$conditionsChilds[$electro];
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

        $caregiver = DependentCaregiver::where('user_id', $caregiverUser->id)->first();

        return DependentCaregiver::updateOrCreate(
            ['id' => $caregiver?->id],
            [
                'dependent_user_id' => $dependentUser->id,
                'user_id' => $caregiverUser->id,
                'relative' => $row['parentesco_cuidador'],
                'healthcare_type' => $this->formatField($row['prevision_cuidador'], 'healthcare'),
                'empam' => $this->formatField($row['empam_cuidador'], 'boolean'),
                'zarit' => $this->formatField($row['zarit_cuidador'], 'boolean'),
                'immunizations' => $row['inmunizaciones_cuidador'],
                'elaborated_plan' => $this->formatField($row['plan_elaborado_cuidador'], 'boolean'),
                'evaluated_plan' => $this->formatField($row['plan_evaluado_cuidador'], 'boolean'),
                'trained' => $this->formatField($row['capacitacion_cuidador'], 'boolean'),
                'stipend' => $this->formatField($row['estipendio_cuidador'], 'boolean')
            ]
        );
    }

    public function formatField($value, $type)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        switch ($type) {
            case 'boolean':
                $value = strtolower(trim($value));
                return $value == 'si' || $value == 'ok' ? true : ($value == 'no' || $value == 'p' ? false : null);

            case 'integer':
                $clean = intval(trim($value));
                return $clean != 0 ? strval($clean) : null;

            case 'date':
                try {
                    $value = intval(trim($value));
                    if ($value == 0) return null;
                    return Date::excelToDateTimeObject($value)->format($this->date_format);
                } catch (\Exception $e) {
                    Log::warning('Error parseando fecha', ['value' => $value]);
                    return null;
                }

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

            case 'text':
            default:
                return trim($value);
        }
    }

    public function chunkSize(): int
    {
        return 50;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function () {
                Log::info('=== INICIO IMPORTACIÓN DEPENDENT USERS ===');
            },
            AfterImport::class => function () {
                Log::info('=== FIN IMPORTACIÓN ===', [
                    'insertados' => self::$insertedCount,
                    'actualizados' => self::$updatedCount,
                    'omitidos' => self::$skippedCount
                ]);

                Notification::make()
                    ->title('Importación Completada')
                    ->success()
                    ->body(self::getCompletedNotificationBody())
                    ->send();
            },
        ];
    }

    public static function getCompletedNotificationBody(): string
    {
        $body = 'La importación de usuarios dependientes ha finalizado. ';
        $body .= self::$insertedCount . ' insertada(s), ';
        $body .= self::$updatedCount . ' actualizada(s), ';
        $body .= self::$skippedCount . ' omitida(s).';

        $message = $body;

        self::$insertedCount = 0;
        self::$updatedCount = 0;
        self::$skippedCount = 0;

        return $message;
    }
}
