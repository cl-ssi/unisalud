<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SigteExternalWaitlistImport extends Model
{
    protected $fillable = [
        'uploaded_by',
        'filename',
        'total_count',
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(SigteExternalWaitlistRun::class, 'sigte_external_waitlist_import_id');
    }
}
