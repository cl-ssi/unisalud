<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Waitlist extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'identifier',
        'user_id',
        'plano', // PLANO: vació
        'extremity_id', // EXTREMIDAD: vació
        'wait_health_care_service_id', //PRESTA_EST: Fondo de Ojo
        'cie10_id',
        'sigte_id', // SIGTE_ID	

        'wait_medical_benefit_id', //TIPO PRESTACION'
        'wait_specialty_id', // ESPECIALIDAD
        'organization_id', // ESTABLECIMIENTO ORIGEN

        'commune_id', // COMUNA
        'confirmation_diagnosis', // CONFIR_DIAG

        // CITA
        'status', // NO DERIVADO - DERIVADO - CITADO - ATENDIDO - INASISTENTE - INCONTACTABLE - EGRESADO
        // 'discharge', // ATENCION REALIZADA - RECHAZO - INASISTENCIA - FALLECIMIENTO - CONTACTO NO CORRESPONDE - ATENCION OTORGADA EN EXTRASISTEMA
        'appointment_date', // FECHA_HORA_CITA	

        // ESTAB PRESTADOR
        'destiny_organization_id', // ESTAB_PRESTADOR	
        'attention_date', // FECHA_HORA_ATENCION
        'attended', // ASISTENCIA
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function healthCareService()
    {
        return $this->belongsTo(HealthCareService::class, 'wait_health_care_service_id');
    }

    public function cie10(): BelongsTo
    {
        return $this->belongsTo(Cie10::class, 'cie10_id');
    }

    public function medicalBenefit(): BelongsTo
    {
        return $this->belongsTo(WaitlistMedicalBenefit::class, 'wait_medical_benefit_id');
    }

    public function waitlistSpecialty(): BelongsTo
    {
        return $this->belongsTo(WaitlistSpecialty::class, 'wait_specialty_id');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function destinyOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'destiny_organization_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(WaitlistContact::class, 'waitlist_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WaitlistEvent::class, 'waitlist_id');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WaitlistMessage::class, 'waitlist_id');
    }

    public function extremity(): BelongsTo
    {
        return $this->belongsTo(Extremity::class, 'extremity_id');
    }
    
    protected $table = 'wait_waitlists';
}
