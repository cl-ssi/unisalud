<?php

namespace App\Models;

use App\Enums\SurgicalComplexity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SigteSurgicalExportBatch extends Model
{
    protected $table = 'sigte_surgical_exports';

    protected $fillable = [
        'exported_by',
        'desde',
        'hasta',
        'requesting_professional_id',
        'status',
        'complexity',
        'patients_count',
    ];

    protected $casts = [
        'desde'      => 'date',
        'hasta'      => 'date',
        'complexity' => SurgicalComplexity::class,
    ];

    public function exportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }

    public function requestingProfessional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_professional_id');
    }

    public function waitlistEntries(): BelongsToMany
    {
        return $this->belongsToMany(
            SigteSurgicalWaitlist::class,
            'sigte_surgical_export_waitlist',
            'sigte_surgical_export_id',
            'sigte_surgical_waitlist_id',
        )->withPivot('created_at');
    }
}
