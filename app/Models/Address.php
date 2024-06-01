<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\AddressUse;
use App\Enums\AddressType;

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

    protected $casts = [
        'use'   => AddressUse::class,
        'type'  => AddressType::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(Location::class);
    }
}
