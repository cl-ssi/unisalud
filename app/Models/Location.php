<?php

namespace App\Models;

use App\Models\Appointment;
use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Vancuren\PhpTurf\PhpTurf as Turf;

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
        'location',
    ];

    protected $appends = [
        'location',
    ];


    protected $casts = [
        'processed' => 'bool',
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
            // $this->attributes['latitude'] = $location['lat'];
            // $this->attributes['longitude'] = $location['lng'];
            $this->latitude = $location['lat'];
            $this->longitude = $location['lng'];
            // unset($this->attributes['location']);
            $this->location = null;
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

    public function getFloodedAttribute(): ?bool
    {
        $contents = file_get_contents(base_path('public/json/cota_30_tarapaca.geojson'));        
        $json = json_decode(json: $contents, associative: true);
        if (is_null($this->location['lat']) || is_null($this->location['lng']) || !isset($json['features'])) {
            return null;
        }
        $point = new Turf\Point([$this->location['lng'], $this->location['lat']]);
        $polygon = new Turf\Polygon([$json['features'][0]['geometry']['coordinates'][0]]);
        $flood = $polygon->containsPoint($point);
        // dd($point, $polygon, $flood);
        return $flood;
    }

    public function getAlluviumAttribute(): ?bool
    {
        $contents = file_get_contents(base_path('public/json/UTF-81_Aluvion.geojson'));        
        $json = json_decode(json: $contents, associative: true);
        if (is_null($this->location['lat']) || is_null($this->location['lng']) || !isset($json['features'])) {
            return null;
        }
        $point = new Turf\Point([$this->location['lng'], $this->location['lat']]);
        $polygon1 = new Turf\Polygon([$json['features'][0]['geometry']['geometries'][0]['coordinates'][0]]);
        $polygon2 = new Turf\Polygon([$json['features'][0]['geometry']['geometries'][1]['coordinates'][0]]);
        $polygon3 = new Turf\Polygon([$json['features'][0]['geometry']['geometries'][2]['coordinates'][0]]);
        $alluvium1 = $polygon1->containsPoint($point);
        $alluvium2 = $polygon2->containsPoint($point);
        $alluvium3 = $polygon3->containsPoint($point);
        return $alluvium1 || $alluvium2 || $alluvium3;
    }
    
    protected $table = 'locations';
}