<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SigteExternalWaitlistRun extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'sigte_external_waitlist_import_id',
        'run',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(SigteExternalWaitlistImport::class, 'sigte_external_waitlist_import_id');
    }
}
