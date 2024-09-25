<?php

namespace App\Models;

use App\Models\Some\Appointment;
use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'status',
        'name',
        'alias',
        'description',
        'address_id',
        'longitude',
        'latitude',
        'organization_id',
    //   'location',
    ];

    protected $appends = [
        'location',
    ];

    public function appointments()
    {
        return $this->morphToMany(Appointment::class, 'appointable');
    }

    public function organization(){
        return $this->belongsTo('App\Models\Organization', 'organization_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(User::class, 'address_id');
    }

    /**
    * Returns the 'latitude' and 'longitude' attributes as the computed 'location' attribute,
    * as a standard Google Maps style Point array with 'lat' and 'lng' attributes.
    *
    * Used by the Filament Google Maps package.
    *
    * Requires the 'location' attribute be included in this model's $fillable array.
    *
    * @return array
    */

    public function getLocationAttribute(): array
    {
        return [
            "lat" => (float)$this->latitude,
            "lng" => (float)$this->longitude,
        ];
    }

    /**
    * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
    * 'latitude' and 'longitude' attributes on this model.
    *
    * Used by the Filament Google Maps package.
    *
    * Requires the 'location' attribute be included in this model's $fillable array.
    *
    * @param ?array $location
    * @return void
    */
    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location))
        {
            $this->attributes['latitude'] = $location['lat'];
            $this->attributes['longitude'] = $location['lng'];
            unset($this->attributes['location']);
        }
    }

    /**
     * Get the lat and lng attribute/field names used on this table
     *
     * Used by the Filament Google Maps package.
     *
     * @return string[]
     */
    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'latitude',
            'lng' => 'longitude',
        ];
    }

   /**
    * Get the name of the computed location attribute
    *
    * Used by the Filament Google Maps package.
    *
    * @return string
    */
    public static function getComputedLocation(): string
    {
        return 'location';
    }

    protected $table = 'locations';
}