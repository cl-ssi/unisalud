<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DemandManagementWaitlist extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * PLANO codes from the SIGTE export's DEIS codification.
     */
    public const PLANO_LABELS = [
        '1' => 'Izquierdo',
        '2' => 'Derecho',
        '3' => 'Superior',
        '4' => 'Inferior',
        '5' => 'Medio',
    ];

    /**
     * EXTREMIDAD codes from the SIGTE export's DEIS codification.
     */
    public const EXTREMITY_LABELS = [
        '1'  => 'Dedo',
        '2'  => 'Mano',
        '3'  => 'Brazo',
        '4'  => 'Codo',
        '5'  => 'Muñeca',
        '6'  => 'Antebrazo',
        '7'  => 'Hombro',
        '8'  => 'Pie',
        '9'  => 'Tobillo',
        '10' => 'Rodilla',
        '11' => 'Muslo',
        '12' => 'Sacroilíaca',
        '13' => 'Cadera',
        '14' => 'Pierna',
        '15' => 'Acromio-clavicular',
        '16' => 'Esterno-clavicular',
        '17' => 'Cúbito',
        '18' => 'Radio',
        '19' => 'Cervical',
        '20' => 'Dorsal',
        '21' => 'Lumbar',
        '22' => 'Párpado superior',
        '23' => 'Párpado inferior',
        '24' => 'Pulgar',
        '25' => 'Columna',
    ];

    /**
     * C_SALIDA (causal de egreso) codes from the SIGTE export, named as in
     * "Causales de egreso v8" (Norma Nº 118). 3 and 20 are standby marks
     * ("Condiciones" sheet): the user stays on the waitlist.
     */
    public const EXIT_CODE_LABELS = [
        '0'  => 'GES',
        '1'  => 'Atención Realizada',
        '2'  => 'Procedimiento y Exámenes de Apoyo de Diagnóstico y Terapéuticos',
        '3'  => 'Indicación Médica Para Reevaluación',
        '4'  => 'Atención otorgada en el extra sistema',
        '5'  => 'No Beneficiario',
        '6'  => 'Renuncia o Rechazo Voluntario',
        '7'  => 'Recuperación Espontánea',
        '8'  => 'Inasistencias',
        '9'  => 'Fallecimiento',
        '10' => 'Solicitud de Indicación Duplicada',
        '11' => 'Contacto no corresponde',
        '12' => 'No corresponde realizar cirugía',
        '13' => 'Traslado coordinado',
        '14' => 'No Pertinencia',
        '15' => 'Error de Digitación',
        '16' => 'Atención por Resolutividad',
        '17' => 'Atención por Telemedicina',
        '18' => 'Modificación de la condición clínico-diagnóstica del caso',
        '19' => 'Atención por Hospital Digital',
        '20' => 'Postergaciones',
        '99' => 'Técnico Administrativo Nivel Central',
    ];

    /**
     * C_SALIDA codes that keep a case open even with an exit date
     * (Indicación Médica Para Reevaluación, Postergaciones).
     */
    public const STANDBY_EXIT_CODES = ['3', '20'];

    /**
     * C_SALIDA compared to STANDBY_EXIT_CODES trimmed and without leading
     * zeros, so "03" counts as 3 ("0" becomes "", which is not a standby code).
     */
    protected const NORMALIZED_EXIT_CODE_SQL = "TRIM(LEADING '0' FROM TRIM(exit_code))";

    /**
     * Egresados: cases with both F_SALIDA and C_SALIDA, except C_SALIDA 3
     * and 20, which stay open. PRESTA_MIN_SALIDA is not required: it was
     * added to the rules recently and older discharges don't have it.
     */
    public function scopeDischarged(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $standby = implode(',', array_map(fn ($code) => "'{$code}'", self::STANDBY_EXIT_CODES));

        $query->whereNotNull('exit_date')
            ->whereNotNull('exit_code')
            ->whereRaw("TRIM(exit_code) <> ''")
            ->whereRaw(self::NORMALIZED_EXIT_CODE_SQL . " NOT IN ({$standby})");
    }

    /**
     * scopeDischarged() for one case's values: F_SALIDA and C_SALIDA, with
     * C_SALIDA not a standby code (3, 20).
     */
    public static function isDischarged($exitDate, ?string $exitCode): bool
    {
        $code = trim((string) $exitCode);

        return filled($exitDate)
            && $code !== ''
            && ! in_array(ltrim($code, '0'), self::STANDBY_EXIT_CODES, true);
    }

    /**
     * Abiertos: every case that is not discharged (scopeDischarged).
     */
    public function scopeOpen(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $standby = implode(',', array_map(fn ($code) => "'{$code}'", self::STANDBY_EXIT_CODES));

        $query->where(fn ($q) => $q
            ->whereNull('exit_date')
            ->orWhereNull('exit_code')
            ->orWhereRaw("TRIM(exit_code) = ''")
            ->orWhereRaw(self::NORMALIZED_EXIT_CODE_SQL . " IN ({$standby})"));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'health_service_id',                // SERV_SALUD
        'identifier',
        'user_id',
        'minsal_specialty_id',              // PRESTA_MIN (TIPO_PREST 1)
        'sigte_procedure_id',               // PRESTA_MIN (TIPO_PREST 3)
        'plano',                            // PLANO
        'extremity',                        // EXTREMIDAD
        'wait_health_care_service_id',      // PRESTA_EST
        'entry_date',                       // F_ENTRADA
        'origin_establishment_id',          // ESTAB_ORIG
        'destiny_establishment_id',         // ESTAB_DEST
        'exit_date',                        // F_SALIDA
        'exit_code',                        // C_SALIDA
        'referring_specialty',              // E_OTOR_AT 
        'exit_minsal_specialty_id',         // PRESTA_MIN_SALIDA (TIPO_PREST 1)
        'exit_sigte_procedure_id',          // PRESTA_MIN_SALIDA (TIPO_PREST 3)
        'prais',                            // PRAIS
        'region_id',                        // REGION
        'commune_id',                       // COMUNA
        'suspected_diagnosis',              // SOSPECHA_DIAG
        'confirmed_diagnosis',              // CONFIR_DIAG
        'cie10_id',                         // CIE-10
        'appointment_date',                 // F_CITACION
        'requesting_professional_id',       // RUN_PROF_SOL
        'resolving_professional_id',        // RUN_PROF_RESOL
        'sigte_id',                         // SIGTE_ID
        'healthcare_type_id',               // PREVISION
        'specialty_id',                     // ESPECIALIDAD; PRESTA_MIN (TIPO_PREST 4)
        'exit_specialty_id',                // PRESTA_MIN_SALIDA (TIPO_PREST 4)
        'establishment_id',                 // ESTABLECIMIENTO
        'pediatric',                        // PEDIATRICO
        'lb',                               // LB
        'waitlist_entry_type_id',           // TIPO_PREST
        'status',
        'ges',                              // GES
        'local_id',                        // ID_LOCAL
        'result',                            // RESULTADO
        'waitlistAge',                      // EDAD
        'waitlistYear',                     // AÑO
        'worker',                           // Funcionario
        'iqType',                           // Típo de IQ
        'oncologic',                        // Oncologico
        'origin_commune_id',                // Comuna Origen
        'fonasa',                           // Fonasa
        'praisUser',                        // Usuario PRAIS
        'lbPrais',                          // LB PRAIS
        'lbUrinary',                        // LB INCONTINENCIA URINARIA
        'exitError',                        // Error Egreso
        'lbIqOdonto',                       // LB IQ ODONTO
        'procedureType',                    // Tipo Procedimiento
        'sename',                           // SENAME
        'wait_medical_benefit_id',           // TIPO_PRESTACION
        'elapsed_days'
    ];

    protected $casts = [
        'entry_date'        => 'date',
        'exit_date'         => 'date',
        'appointment_date'  => 'date',
        'ges'               => 'boolean',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resolvingProfessional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolving_professional_id');
    }

    public function requestingProfessional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_professional_id');
    }

    public function cie10(): BelongsTo
    {
        return $this->belongsTo(Cie10::class, 'cie10_id');
    }

    /**
     * GES health problems the case's CIE-10 code belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, GesHealthProblem>
     */
    public function gesProblems(): \Illuminate\Database\Eloquent\Collection
    {
        return GesHealthProblem::forCie10Code($this->cie10?->code);
    }

    public function waitlistSpecialty(): BelongsTo
    {
        return $this->belongsTo(DemandManagementSpeciality::class, 'specialty_id');
    }

    public function exitWaitlistSpecialty(): BelongsTo
    {
        return $this->belongsTo(DemandManagementSpeciality::class, 'exit_specialty_id');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'establishment_id');
    }

    public function originCommune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'origin_commune_id');
    }

    public function originEstablishment(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'origin_establishment_id');
    }

    public function entryType(): BelongsTo
    {
        return $this->belongsTo(WaitlistEntryType::class, 'waitlist_entry_type_id');
    }

    public function destinyEstablishment(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'destiny_establishment_id');
    }

    public function healthcareType(): BelongsTo
    {
        return $this->belongsTo(HealthcareType::class, 'healthcare_type_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(DemandManagementContact::class, 'waitlist_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DemandManagementEvent::class, 'waitlist_id');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WaitlistMessage::class, 'waitlist_id');
    }

    public function healthcareService(): BelongsTo
    {
        return $this->belongsTo(DemandManagementHealthCareService::class, 'demand_management_health_care_services');
    }

    public function medicalBenefit(): BelongsTo
    {
        return $this->belongsTo(DemandManagementMedicalBenefit::class, 'wait_medical_benefit_id');
    }

    public function minsalSpecialty(): BelongsTo
    {
        return $this->belongsTo(MinsalSpecialty::class, 'minsal_specialty_id');
    }

    public function minsalExitSpecialty(): BelongsTo
    {
        return $this->belongsTo(MinsalSpecialty::class, 'exit_minsal_specialty_id');
    }

    public function sigteProcedure(): BelongsTo
    {
        return $this->belongsTo(SigteProcedure::class, 'sigte_procedure_id');
    }

    public function exitSigteProcedure(): BelongsTo
    {
        return $this->belongsTo(SigteProcedure::class, 'exit_sigte_procedure_id');
    }

    public function establishmentHealthCareService(): BelongsTo
    {
        return $this->belongsTo(DemandManagementHealthCareService::class, 'wait_health_care_service_id');
    }

    protected $table = 'demand_management_waitlists';
}
