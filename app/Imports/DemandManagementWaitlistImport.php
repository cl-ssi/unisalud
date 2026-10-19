<?php

namespace App\Imports;

use App\Enums\Sex;
use App\Models\Address;
use App\Models\Commune;
use App\Models\ContactPoint;
use App\Models\HealthcareType;
use App\Models\HumanName;
use App\Models\Identifier;
use App\Models\MinsalSpecialty;
use App\Models\DemandManagementHealthCareService;
use App\Models\DemandManagementMedicalBenefit;
use App\Models\DemandManagementSpeciality;
use App\Models\DemandManagementWaitlist;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\WaitlistEntryType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Imports the SIGTE demand management waitlist template ("Libro1.xlsx").
 *
 * Runs synchronously (no ShouldQueue) like the other maatwebsite imports
 * triggered from the panel, so it doesn't depend on a queue worker.
 */
class DemandManagementWaitlistImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
    /**
     * SEXO in the SIGTE export uses the DEIS "sexo biológico" codification,
     * not free text — map it directly instead of resolving through the
     * sexes lookup table (whose autoincrement ids don't correspond to these codes).
     */
    public const SEX_CODE_MAP = [
        '1'  => Sex::Male->value,
        '2'  => Sex::Female->value,
        '3'  => Sex::Intersex->value,
        '93' => Sex::NotInformed->value,
        '99' => Sex::Unknown->value,
    ];

    protected static array $healthCareServiceCache = [];
    protected static array $specialtyCache = [];
    protected static array $medicalBenefitCache = [];
    protected static array $communeCodeCache = [];
    protected static array $organizationCodeCache = [];
    protected static array $organizationAliasCache = [];
    protected static array $healthcareTypeCache = [];
    protected static array $minsalSpecialtyCache = [];
    protected static array $entryTypeCache = [];
    protected static array $regionCache = [];
    protected static array $originCommuneNameCache = [];
    protected static bool $initialized = false;

    public int $successfulRows = 0;
    public array $failedRows = [];

    protected static function ensureCachesAreLoaded(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$communeCodeCache = Commune::query()
            ->select('id', 'code_deis')
            ->get()
            ->pluck('id', 'code_deis')
            ->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])
            ->toArray();

        $organizations = Organization::query()
            ->select('id', 'code_deis', 'alias')
            ->get();

        self::$organizationCodeCache = $organizations->pluck('id', 'code_deis')
            ->filter()
            ->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])
            ->toArray();

        self::$organizationAliasCache = $organizations->pluck('id', 'alias')
            ->filter()
            ->mapWithKeys(fn($id, $alias) => [strtolower(trim($alias)) => $id])
            ->toArray();

        self::$healthCareServiceCache = DemandManagementHealthCareService::all()->pluck('id', 'text')->mapWithKeys(fn($id, $text) => [strtolower(trim($text)) => $id])->toArray();
        self::$specialtyCache = DemandManagementSpeciality::all()->pluck('id', 'text')->mapWithKeys(fn($id, $text) => [strtolower(trim($text)) => $id])->toArray();
        self::$medicalBenefitCache = DemandManagementMedicalBenefit::all()->pluck('id', 'text')->mapWithKeys(fn($id, $text) => [strtolower(trim($text)) => $id])->toArray();
        self::$healthcareTypeCache = HealthcareType::all()->pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])->toArray();
        self::$minsalSpecialtyCache = MinsalSpecialty::all()->pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])->toArray();
        self::$entryTypeCache = WaitlistEntryType::all()->pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])->toArray();
        self::$regionCache = Region::all()->pluck('id', 'id')->mapWithKeys(fn($id, $regionId) => [strtolower(trim($regionId)) => $id])->toArray();

        self::$originCommuneNameCache = Commune::query()
            ->select('id', 'name')
            ->get()
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        self::$initialized = true;
    }

    public function onRow(Row $row): void
    {
        self::ensureCachesAreLoaded();

        $data = array_map(fn($value) => is_string($value) ? trim($value) : $value, $row->toArray());

        // Skip fully empty rows (trailing formatted rows in the template).
        if (collect($data)->filter(fn($value) => filled($value))->isEmpty()) {
            return;
        }

        try {
            $this->importRow($data);
            $this->successfulRows++;
        } catch (\Throwable $e) {
            $this->failedRows[] = ['row' => $row->getIndex(), 'error' => $e->getMessage()];
            Log::error('DemandManagementWaitlistImport: error en fila ' . $row->getIndex(), [
                'sigte_id' => $data['sigte_id'] ?? null,
                'run'      => $data['run'] ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    protected function importRow(array $data): DemandManagementWaitlist
    {
        return DB::transaction(function () use ($data) {
            // Headings arrive slugged by WithHeadingRow ("TIPO PRESTACION" → "tipo_prestacion",
            // "AÑO" → "ano"), so look keys up by the template's original header name.
            $val = function ($key, $default = null) use ($data) {
                $value = $data[Str::slug($key, '_')] ?? null;

                return ($value === null || $value === '') ? $default : $value;
            };

            $text = strtolower(trim($val('PRESTA_EST', '')));
            $healthCareServiceId = self::$healthCareServiceCache[$text] ?? null;
            if (is_null($healthCareServiceId) && !empty($text)) {
                $healthCareService = DemandManagementHealthCareService::firstOrCreate(['text' => $text]);
                self::$healthCareServiceCache[$text] = $healthCareService->id;
                $healthCareServiceId = $healthCareService->id;
            }

            $runValue = $val('RUN');
            $user = null;
            if (!empty($runValue)) {
                $user = User::whereHas(
                    'identifiers',
                    fn($query) =>
                    $query->where('value', $runValue)->where('cod_con_identifier_type_id', 1)
                )->first();
            }

            $sexoRaw = trim((string) $val('SEXO', ''));
            $sexoCode = is_numeric($sexoRaw) ? (string) intval($sexoRaw) : $sexoRaw;
            $sexValue = self::SEX_CODE_MAP[$sexoCode] ?? null;
            $isNewUser = is_null($user);

            $userCreatedOrUpdated = User::updateOrCreate(
                ['id' => optional($user)->id],
                [
                    'active'            => 1,
                    'text'              => $val('NOMBRES') . ' ' . $val('PRIMER_APELLIDO') . ' ' . $val('SEGUNDO_APELLIDO'),
                    'given'             => $val('NOMBRES'),
                    'fathers_family'    => $val('PRIMER_APELLIDO'),
                    'mothers_family'    => $val('SEGUNDO_APELLIDO'),
                    'birthday'          => $this->parseFlexibleDate($val('FECHA_NAC')),
                    'sex'               => $sexValue,
                ]
            );

            if ($isNewUser && !empty($runValue)) {
                // SE CREA IDENTIFIER
                Identifier::create([
                    'user_id'                 => $userCreatedOrUpdated->id,
                    'use'                     => 'official',
                    'cod_con_identifier_type_id'  => 1,
                    'value'                   => $runValue,
                    'dv'                      => $val('DV')
                ]);

                //SE CREA HUMAN NAME
                HumanName::create([
                    'use'              => 'official',
                    'given'            => $val('NOMBRES'),
                    'fathers_family'   => $val('PRIMER_APELLIDO'),
                    'mothers_family'   => $val('SEGUNDO_APELLIDO'),
                    'period_start'     => now(),
                    'user_id'          => $userCreatedOrUpdated->id
                ]);
            }

            $communeId = self::$communeCodeCache[strtolower(trim($val('COMUNA', '')))] ?? null;
            $originOrganizationId = self::$organizationCodeCache[strtolower(trim($val('ESTAB_ORIG', '')))] ?? null;
            $destinyOrganizationId = self::$organizationCodeCache[strtolower(trim($val('ESTAB_DEST', '')))] ?? null;

            $alias = strtolower(trim($val('ESTABLECIMIENTO', 'sin establecimiento')));
            $organizationId = self::$organizationAliasCache[$alias] ?? null;

            $previsionCode = strtolower(trim($val('PREVISION', '')));
            $previsionValue = self::$healthcareTypeCache[$previsionCode] ?? null;

            $minsalSpecialty = self::$minsalSpecialtyCache[strtolower(trim($val('PRESTA_MIN', '')))] ?? null;
            $minsalExitSpecialty = self::$minsalSpecialtyCache[strtolower(trim($val('PRESTA_MIN_SALIDA', '')))] ?? null;

            $entryType = self::$entryTypeCache[strtolower(trim($val('TIPO_PREST', '')))] ?? null;

            $region = self::$regionCache[strtolower(trim($val('REGION', '')))] ?? null;


            $address = $userCreatedOrUpdated->addresses()->where('use', 'home')->first();

            Address::updateOrCreate(
                ['id' => optional($address)->id, 'user_id' => $userCreatedOrUpdated->id],
                [
                    'use'           => 'home',
                    'type'          => 'physical',
                    'text'          => $val('NOM_CALLE'),
                    'line'          => $val('NUM_DIRECCION'),
                    'suburb'        => $val('RESTO_DIRECCION'),
                    'city'          => $val('CIUDAD'),
                    'commune_id'    => $communeId,
                    'is_rural'      => match ((string) $val('COND_RURALIDAD')) {
                        '1' => false,
                        '2' => true,
                        default => null,
                    },
                    'via'           => match ((string) $val('VIA_DIRECCION')) {
                        '1' => 'calle',
                        '2' => 'pasaje',
                        '3' => 'avenida',
                        '4' => 'otro',
                        default => null,
                    },
                ]
            );

            $contactPointHome = $userCreatedOrUpdated->contactPoints()->where('use', 'home')->first();
            ContactPoint::updateOrCreate(
                ['id' => optional($contactPointHome)->id, 'user_id' => $userCreatedOrUpdated->id, 'system' => 'phone', 'use' => 'home'],
                ['value' => $val('FONO_FIJO'), 'actually' => 0]
            );

            $contactPointMobile = $userCreatedOrUpdated->contactPoints()->where('use', 'mobile')->first();
            ContactPoint::updateOrCreate(
                ['id' => optional($contactPointMobile)->id, 'user_id' => $userCreatedOrUpdated->id, 'system' => 'phone', 'use' => 'mobile'],
                ['value' => $val('FONO_MOVIL'), 'actually' => 0]
            );

            $contactPointEmail = $userCreatedOrUpdated->contactPoints()->where('use', 'work')->first();
            ContactPoint::updateOrCreate(
                ['id' => optional($contactPointEmail)->id, 'user_id' => $userCreatedOrUpdated->id, 'system' => 'email', 'use' => 'work'],
                ['value' => $val('EMAIL'), 'actually' => 0]
            );


            $textSpecialty = strtolower(trim($val('ESPECIALIDAD', 'sin especialidad')));
            $specialtyId = self::$specialtyCache[$textSpecialty] ?? null;
            if (is_null($specialtyId)) {
                $specialty = DemandManagementSpeciality::firstOrCreate(['text' => $textSpecialty]);
                self::$specialtyCache[$textSpecialty] = $specialty->id;
                $specialtyId = $specialty->id;
            }

            $profSolCreatedOrUpdated = $this->findOrCreateProfessional($val('RUN_PROF_SOL'), $val('DV_PROF_SOL'));
            $profResolCreatedOrUpdated = $this->findOrCreateProfessional($val('RUN_PROF_RESOL'), $val('DV_PROF_RESOL'));


            $textMedicalBenefits = strtolower(trim($val('TIPO PRESTACION', '')));
            $medicalBenefitId = self::$medicalBenefitCache[$textMedicalBenefits] ?? null;
            if (is_null($medicalBenefitId)) {
                $medicalBenefits = DemandManagementMedicalBenefit::firstOrCreate(['text' => $textMedicalBenefits]);
                self::$medicalBenefitCache[$textMedicalBenefits] = $medicalBenefits->id;
                $medicalBenefitId = $medicalBenefits->id;
            }

            $originCommuneId = self::$originCommuneNameCache[strtolower(trim($val('Comuna Origen', '')))] ?? null;

            $waitlistCreatedOrUpdated = DemandManagementWaitlist::updateOrCreate(
                [
                    'user_id'                => $userCreatedOrUpdated->id,
                    'sigte_id'               => $val('SIGTE_ID'),
                ],
                [
                    'specialty_id'              => $specialtyId,
                    'origin_establishment_id'   => $originOrganizationId,
                    'wait_health_care_service_id' => $healthCareServiceId,
                    'commune_id'                => $communeId,
                    'status'                    => ($val('ESTADO') !== null) ? strtolower(trim($val('ESTADO'))) : null,
                    'destiny_establishment_id'  => $destinyOrganizationId,
                    'suspected_diagnosis'       => ($val('SOSPECHA_DIAG') !== null) ? strtolower(trim($val('SOSPECHA_DIAG'))) : null,
                    'establishment_id'          => $organizationId,
                    'entry_date'                => $this->parseFlexibleDate($val('F_ENTRADA')),
                    'exit_date'                 => $this->parseFlexibleDate($val('F_SALIDA')),
                    'healthcare_type_id'        => $previsionValue,
                    'minsal_specialty_id'       => $minsalSpecialty,
                    'exit_minsal_specialty_id'  => $minsalExitSpecialty,
                    'plano'                     => ($val('PLANO') !== null) ? strtolower(trim($val('PLANO'))) : null,
                    'extremity'                 => ($val('EXTREMIDAD') !== null) ? strtolower(trim($val('EXTREMIDAD'))) : null,
                    'prais'                     => ($val('PRAIS') !== null) ? strtolower(trim($val('PRAIS'))) : null,
                    'region_id'                 => $region,
                    'pediatric'                 => $val('PEDIATRICO'),
                    'lb'                        => ($val('LB') !== null) ? strtolower(trim($val('LB'))) : null,
                    'requesting_professional_id' => optional($profSolCreatedOrUpdated)->id,
                    'resolving_professional_id' => optional($profResolCreatedOrUpdated)->id,
                    'waitlist_entry_type_id'    => $entryType,
                    'local_id'                  => $val('ID_LOCAL'),
                    'result'                    => $val('RESULTADO'),
                    'waitlistAge'               => $val('EDAD') ? floatval(str_replace(',', '.', $val('EDAD'))) : null,
                    'waitlistYear'              => $val('AÑO'),
                    'health_service_id'         => $val('SERV_SALUD'),
                    'appointment_date'          => $this->parseDateTime($val('F_CITACION')),
                    'worker'                    => $val('FUNCIONARIO'),
                    'iqType'                    => $val('Tipo de IQ'),
                    'oncologic'                 => $val('Oncologico'),
                    'origin_commune_id'         => $originCommuneId,
                    'fonasa'                    => $val('FONASA'),
                    'praisUser'                 => $val('Usuario PRAIS'),
                    'lbPrais'                   => $val('LB PRAIS'),
                    'lbUrinary'                 => $val('LB INCONTINENCIA URINARIA'),
                    'exitError'                 => $val('Error Egreso'),
                    'lbIqOdonto'                => $val('LB IQ ODONTO'),
                    'procedureType'             => $val('Tipo Procedimiento'),
                    'sename'                    => $val('SENAME'),
                    'exit_code'                 => filled($val('C_SALIDA')) ? trim($val('C_SALIDA')) : null,
                    'referring_specialty'       => $val('E_OTOR_AT'),
                    'wait_medical_benefit_id'   => $medicalBenefitId,
                    'elapsed_days'              => $val('DIAS_PASADOS'),
                ]
            );

            if ($waitlistCreatedOrUpdated->events()->count() == 0) {
                $waitlistCreatedOrUpdated->events()->create([
                    'status'            => ($val('ESTADO') ?? null) ? strtolower(trim($val('ESTADO'))) : null,
                    'registered_at'     => now(),
                    'text'              => 'Registrado a través de carga masiva',
                    'discharge'         => strtolower(trim($val('CAUSAL_EGRESO') ?? '')),
                    'appointment_at'    => $this->parseDateTime($val('FECHA_ATENCION')),
                    'register_user_id'  => auth()->id() ?? User::first()?->id // Fallback if no user is authenticated
                ]);
            }

            if ($waitlistCreatedOrUpdated->contacts()->count() == 0) {
                $estado = strtolower(trim($val('ESTADO', '')));
                $statusContact = null;

                if (in_array($estado, ['citado', 'atendido', 'inasistente', 'egresado'])) {
                    $statusContact = 'si';
                } elseif ($estado == 'incontactable') {
                    $statusContact = 'no';
                }

                $waitlistCreatedOrUpdated->contacts()->create([
                    'type'              => 'telefonico',
                    'status'            => $statusContact,
                    'contacted_at'      => now(),
                    'text'              => 'Registrado a través de carga masiva',
                    'register_user_id'  => auth()->id() ?? User::first()?->id // Fallback if no user is authenticated
                ]);
            }

            return $waitlistCreatedOrUpdated;
        });
    }

    protected function findOrCreateProfessional($run, $dv): ?User
    {
        $run = trim((string) $run);
        if (empty($run)) {
            return null;
        }

        $user = User::whereHas(
            'identifiers',
            fn($query) =>
            $query->where('value', $run)->where('cod_con_identifier_type_id', 1)
        )->first();

        if ($user) {
            $user->update(['active' => 1]);
        } else {
            $user = User::create(['active' => 1]);
            Identifier::create([
                'user_id'                  => $user->id,
                'use'                      => 'official',
                'cod_con_identifier_type_id' => 1,
                'value'                    => $run,
                'dv'                       => $dv,
            ]);
        }

        return $user;
    }

    /**
     * SIGTE exports have used both "d/m/Y" and "d-m-Y" for date columns
     * depending on the source, and .xlsx files may hold real date cells
     * (Excel serial numbers). Return null instead of throwing on an
     * unparseable value so one bad date doesn't fail the whole row.
     */
    protected function parseFlexibleDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d');
        }

        $value = trim((string) $value);
        $format = str_contains($value, '/') ? 'd/m/Y' : 'd-m-Y';

        try {
            return Carbon::createFromFormat($format, $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseDateTime($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d H:i:s');
        }

        $timestamp = strtotime((string) $value);

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
