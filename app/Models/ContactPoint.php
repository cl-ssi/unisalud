<?php

namespace App\Models;

use App\Models\User;
use App\Models\Em;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContactPoint extends Model
{
    use HasFactory;

    // FIXME: agregar todos los attributos
    protected $fillable = [
        'system',
        'contact_point_id',
        'user_id',
        'location_id',
        'emergency_contact_id',
        'value',
        'organization_id',
        'use',
        'rank',
        'actually'
    ];

    // TODO: Hacer las relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
