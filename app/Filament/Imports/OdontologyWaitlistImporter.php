<?php

namespace App\Filament\Imports;

use App\Models\Address;
use App\Models\Commune;
use App\Models\ContactPoint;
use App\Models\HealthcareType;
use App\Models\HumanName;
use App\Models\Identifier;
use App\Models\MinsalSpecialty;
use App\Models\OdontologyHealthCareService;
use App\Models\OdontologySpeciality;
use App\Models\OdontologyWaitlist;
use App\Models\Organization;
use App\Models\Region;
use App\Models\Sex;
use App\Models\User;
use App\Models\WaitlistEntryType;
use App\Models\OdontologyMedicalBenefit;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OdontologyWaitlistImporter extends Importer
{
    protected static ?string $model = OdontologyWaitlist::class;

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
    protected static array $sexCache = [];
    protected static array $originCommuneNameCache = [];


    protected int $chunkSize = 100;

    protected static function initialize(): void
    {
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

        self::$healthCareServiceCache = OdontologyHealthCareService::all()->pluck('id', 'text')->mapWithKeys(fn($id, $text) => [strtolower(trim($text)) => $id])->toArray();
        self::$specialtyCache = OdontologySpeciality::all()->pluck('id', 'text')->mapWithKeys(fn($id, $text) => [strtolower(trim($text)) => $id])->toArray();
        self::$medicalBenefitCache = OdontologyMedicalBenefit::all()->pluck('id', 'text')->mapWithKeys(fn($id, $text) => [strtolower(trim($text)) => $id])->toArray();
        self::$healthcareTypeCache = HealthcareType::all()->pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])->toArray();
        self::$minsalSpecialtyCache = MinsalSpecialty::all()->pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])->toArray();
        self::$entryTypeCache = WaitlistEntryType::all()->pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])->toArray();
        self::$regionCache = Region::all()->pluck('id', 'id')->mapWithKeys(fn($id, $regionId) => [strtolower(trim($regionId)) => $id])->toArray();
        self::$sexCache = Sex::all()->pluck('id', 'value')->mapWithKeys(fn($value, $id) => [strtolower(trim($value)) => $id])->toArray(); // Fix mapping: pluck('id', 'value')

        self::$originCommuneNameCache = Commune::query()
            ->select('id', 'name')
            ->get()
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();
    }


    public function resolveRecord(): ?OdontologyWaitlist
    {

        return DB::transaction(function () {
            // --- 0. Helper for data access ---
            $val = function ($key, $default = null) {
                return $this->originalData[$key] ?? $default;
            };

            $text = strtolower(trim($val('PRESTA_EST', '')));
            $healthCareServiceId = self::$healthCareServiceCache[$text] ?? null;
            if (is_null($healthCareServiceId) && !empty($text)) {
                $healthCareService = OdontologyHealthCareService::firstOrCreate(['text' => $text]);
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

            $sexValue = self::$sexCache[strtolower(trim($val('SEXO')))] ?? null;
            $isNewUser = is_null($user);

            $userCreatedOrUpdated = User::updateOrCreate(
                ['id' => optional($user)->id],
                [
                    'active'            => 1,
                    'text'              => $val('NOMBRES') . ' ' . $val('PRIMER_APELLIDO') . ' ' . $val('SEGUNDO_APELLIDO'),
                    'given'             => $val('NOMBRES'),
                    'fathers_family'    => $val('PRIMER_APELLIDO'),
                    'mothers_family'    => $val('SEGUNDO_APELLIDO'),
                    'birthday'          => !empty($val('FECHA_NAC'))
                        ? Carbon::createFromFormat('d/m/Y', $val('FECHA_NAC'))->format('Y-m-d')
                        : null,
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
                    'is_rural'      => match ($val('COND_RURALIDAD') ?? null) {
                        '1' => false,
                        '2' => true,
                        default => null,
                    },
                    'via'           => match ($val('VIA_DIRECCION') ?? null) {
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
                $specialty = OdontologySpeciality::firstOrCreate(['text' => $textSpecialty]);
                self::$specialtyCache[$textSpecialty] = $specialty->id;
                $specialtyId = $specialty->id;
            }

            $profSolCreatedOrUpdated = $this->findOrCreateProfessional($val('RUN_PROF_SOL'), $val('DV_PROF_SOL'));
            $profResolCreatedOrUpdated = $this->findOrCreateProfessional($val('RUN_PROF_RESOL'), $val('DV_PROF_RESOL'));


            $textMedicalBenefits = strtolower(trim($val('TIPO PRESTACION', '')));
            $medicalBenefitId = self::$medicalBenefitCache[$textMedicalBenefits] ?? null;
            if (is_null($medicalBenefitId)) {
                $medicalBenefits = OdontologyMedicalBenefit::firstOrCreate(['text' => $textMedicalBenefits]);
                self::$medicalBenefitCache[$textMedicalBenefits] = $medicalBenefits->id;
                $medicalBenefitId = $medicalBenefits->id;
            }

            $originCommuneId = self::$originCommuneNameCache[strtolower(trim($val('Comuna Origen', '')))] ?? null;

            $waitlistCreatedOrUpdated = OdontologyWaitlist::updateOrCreate(
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
                    'entry_date'                => $val('F_ENTRADA') ? Carbon::createFromFormat('d/m/Y', $val('F_ENTRADA'))->format('Y-m-d') : null,
                    'exit_date'                 => $val('F_SALIDA') ? Carbon::createFromFormat('d/m/Y', $val('F_SALIDA'))->format('Y-m-d') : null,
                    'healthcare_type_id'        => $previsionValue,
                    'minsal_specialty_id'       => $minsalSpecialty,
                    'exit_minsal_specialty_id'  => $minsalExitSpecialty,
                    'plano'                     => ($val('PLANO') !== null) ? strtolower(trim($val('PLANO'))) : null,
                    'extremity'                 => ($val('EXTREMIDAD') !== null) ? strtolower(trim($val('EXTREMIDAD'))) : null,
                    'prais'                     => ($val('PRAIS') !== null) ? strtolower(trim($val('PRAIS'))) : null,
                    'region_id'                 => $region,
                    'pediatric'                 => $val('PEDIATRICO'),
                    'lb'                        => ($val('LB') !== null) ? strtolower(trim($val('LB'))) : null,
                    'requesting_professional_id' => optional($profSolCreatedOrUpdated)->id, // Use optional() for safety
                    'resolving_professional_id' => optional($profResolCreatedOrUpdated)->id, // Use optional() for safety
                    'waitlist_entry_type_id'    => $entryType,
                    'local_id'                  => $val('ID_LOCAL'),
                    'result'                    => $val('RESULTADO'),
                    'waitlistAge'               => $val('EDAD') ? floatval(str_replace(',', '.', $val('EDAD'))) : null,
                    'waitlistYear'              => $val('AÑO'),
                    'health_service_id'         => $val('SERV_SALUD'),
                    'appointment_date'          => $val('F_CITACION') ? date("Y-m-d H:i:s", strtotime($val('F_CITACION'))) : null,
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
                    'exit_code'                 => $val('C_SALIDA'),
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
                    'appointment_at'    => !empty($val('FECHA_ATENCION')) ? date("Y-m-d H:i:s", strtotime($val('FECHA_ATENCION'))) : null,
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


    protected function findOrCreateProfessional(?string $run, ?string $dv): ?User
    {
        $run = trim($run);
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
            // Create new user
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

    public static function getColumns(): array
    {
        return [];
    }

    public function getChunkSize(): int
    {
        return $this->chunkSize; // Use the property $chunkSize
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your waitlist import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function shouldQueueEachRow(): bool
    {
        return true;
    }
}
