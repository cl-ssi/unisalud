<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OdontologyWaitlist extends Model
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
     * C_SALIDA (causal de egreso) codes from the SIGTE export.
     */
    public const EXIT_CODE_LABELS = [
        '0'  => 'GES',
        '1'  => 'Atención realizada',
        '2'  => 'Procedimiento informado',
        '3'  => 'Indicación médica para reevaluación',
        '4'  => 'Atención otorgada en el extra-sistema',
        '5'  => 'Cambio de asegurador',
        '6'  => 'Renuncia o rechazo voluntario del usuario',
        '7'  => 'Recuperación espontánea',
        '8'  => 'Dos inasistencias',
        '9'  => 'Fallecimiento',
        '10' => 'Solicitud de indicación duplicada',
        '11' => 'Contacto no corresponde',
        '12' => 'No corresponde realizar cirugía',
        '13' => 'Traslado coordinado',
        '14' => 'No pertinencia',
        '15' => 'Error de Registro',
        '16' => 'Atención por Resolutividad',
        '17' => 'Atención por Telemedicina',
        '18' => 'Modificación de la condición clínico-diagnóstica',
        '19' => 'Hospital Digital',
        '20' => 'Suspensión Transitoria',
        '99' => 'Técnico Administrativo Nivel Central',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'health_service_id',                // SERV_SALUD
        'identifier',
        'user_id',
        'minsal_specialty_id',              // PRESTA_MIN
        'plano',                            // PLANO
        'extremity',                        // EXTREMIDAD
        'wait_health_care_service_id',      // PRESTA_EST
        'entry_date',                       // F_ENTRADA
        'origin_establishment_id',          // ESTAB_ORIG
        'destiny_establishment_id',         // ESTAB_DEST
        'exit_date',                        // F_SALIDA
        'exit_code',                        // C_SALIDA
        'referring_specialty',              // E_OTOR_AT 
        'exit_minsal_specialty_id',         // PRESTA_MIN_SALIDA
        'prais',                            // PRAIS
        'pase_odontologico',
        'visible_municipality',
        'region_id',                        // REGION
        'commune_id',                       // COMUNA
        'suspected_diagnosis',              // SOSPECHA_DIAG
        'confirmed_diagnosis',              // CONFIR_DIAG
        'appointment_date',                 // F_CITACION
        'requesting_professional_id',       // RUN_PROF_SOL
        'resolving_professional_id',        // RUN_PROF_RESOL
        'sigte_id',                         // SIGTE_ID
        'healthcare_type_id',               // PREVISION
        'specialty_id',                     // ESPECIALIDAD
        'establishment_id',                 // ESTABLECIMIENTO
        'pediatric',                        // PEDIATRICO
        'lb',                               // LB
        'waitlist_entry_type_id',           // TIPO_PREST
        'status',
        'local_id',                         // ID_LOCAL
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
        'pase_odontologico' => 'boolean',
        'visible_municipality' => 'boolean',
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

    public function waitlistSpecialty(): BelongsTo
    {
        return $this->belongsTo(OdontologySpeciality::class, 'specialty_id');
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
        return $this->hasMany(OdontologyContact::class, 'waitlist_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OdontologyEvent::class, 'waitlist_id');
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
        return $this->belongsTo(OdontologyHealthCareService::class, 'odontology_health_care_services');
    }

    public function medicalBenefit(): BelongsTo
    {
        return $this->belongsTo(OdontologyMedicalBenefit::class, 'wait_medical_benefit_id');
    }

    public function minsalSpecialty(): BelongsTo
    {
        return $this->belongsTo(MinsalSpecialty::class, 'minsal_specialty_id');
    }

    public function minsalExitSpecialty(): BelongsTo
    {
        return $this->belongsTo(MinsalSpecialty::class, 'exit_minsal_specialty_id');
    }

    public function establishmentHealthCareService(): BelongsTo
    {
        return $this->belongsTo(OdontologyHealthCareService::class, 'wait_health_care_service_id');
    }

    protected $table = 'odontology_waitlists';
}
