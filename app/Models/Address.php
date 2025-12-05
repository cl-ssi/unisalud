<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\AddressUseValue;
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
        'country_id',
        'commune_id',
        'postal_code',
        'region_id',
        'actually',
        'organization_id',
        'practitioner_id',
        'is_rural',
        'via'
    ];

    protected $casts = [
        'use'   => AddressUseValue::class,
        'type'  => AddressType::class
    ];

    protected $appends = [
        'full_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function location(): HasOne
    {
        return $this->HasOne(Location::class, 'address_id');
    }

    public function getFullAddressAttribute(): string
    {
        $fullAddress = '';
        if ($this->text && $this->line) {
            $fullAddress .= $this->text . ' ' . $this->line . ($this->apartment ? ' ' . $this->apartment : '');
            $fullAddress .= ($this->commune) ? (', ' . $this->commune->name) : '';
        }

        return $fullAddress;
    }
}
