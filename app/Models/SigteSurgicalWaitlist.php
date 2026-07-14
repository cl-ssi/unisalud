<?php

namespace App\Models;

use App\Enums\SurgicalComplexity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SigteSurgicalWaitlist extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identifier',
        'user_id',
        'requesting_professional_id',
        'resolving_professional_id',
        'health_service_id',
        'waitlist_entry_type_id',
        'complexity',
        'sigte_surgical_procedure_code_id',
        'plano',
        'extremity',
        'entry_date',
        'origin_establishment_id',
        'destiny_establishment_id',
        'referring_specialty',
        'suspected_diagnosis',
        'confirmed_diagnosis',
        'prais',
        'healthcare_type_id',
        'region_id',
        'commune_id',
        'sigte_id',
        'status',
        'exported_at',
        'exported_by',
    ];

    protected $casts = [
        'entry_date'  => 'date',
        'prais'       => 'boolean',
        'complexity'  => SurgicalComplexity::class,
        'exported_at' => 'datetime',
        'identifier'  => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requestingProfessional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_professional_id');
    }

    public function resolvingProfessional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolving_professional_id');
    }

    public function exportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }

    public function exportBatches(): BelongsToMany
    {
        return $this->belongsToMany(
            SigteSurgicalExportBatch::class,
            'sigte_surgical_export_waitlist',
            'sigte_surgical_waitlist_id',
            'sigte_surgical_export_id',
        )->withPivot('created_at');
    }

    public function entryType(): BelongsTo
    {
        return $this->belongsTo(WaitlistEntryType::class, 'waitlist_entry_type_id');
    }

    public function procedureCode(): BelongsTo
    {
        return $this->belongsTo(SigteSurgicalProcedureCode::class, 'sigte_surgical_procedure_code_id');
    }

    public function originEstablishment(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'origin_establishment_id');
    }

    public function destinyEstablishment(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'destiny_establishment_id');
    }

    public function healthcareType(): BelongsTo
    {
        return $this->belongsTo(HealthcareType::class, 'healthcare_type_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    protected $table = 'sigte_surgical_waitlists';
}
