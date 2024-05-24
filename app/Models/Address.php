<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
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
        'address_id',
        'user_id',
        'period_id',
        'use',
        'type',
        'text',
        'line',
        'apartment',
        'suburb',
        'city',
        'commune_id',
        'postal_code',
        'region_id',
        'actually',
        'organization_id',
        'practitioner_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
