<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Samu\Establishment;

class Organization extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'alias', 'sirh_code', 'epi_mail' 
    ];

    use SoftDeletes;

    public function addresses()
    {
        return $this->hasMany(Address::class, 'organization_id');
    }

    public function practitioners()
    {
        return $this->hasMany(Practitioner::class, 'organization_id');
    }
    
    //Addresses
    public function getOfficialFullAddressAttribute()
    {
        $address = $this->addresses()
            ->first(['text', 'line', 'apartment']);
        return "$address->text $address->line $address->apartment";

    }

    public function scopeGetByAddress($query, $text, $line, $apartment, $country_id, $commune_id, $region_id){
        $query->orWhereHas('addresses', function($query) use ($text, $line, $apartment, $country_id, $commune_id, $region_id){
            return $query->where('text', $text)
                    ->where('text', 'like' , '%' . $text . '%')
                    ->where('line', $line)
                    ->when($apartment, function($query, $apartment){
                        return $query->where('apartment', $apartment);
                    })
                    ->where('country_id', $country_id)
                    ->where('commune_id', $commune_id)
                    ->where('region_id', $region_id);
        });
    
    }


    public function samu()
    {
        return $this->belongsTo(Establishment::class,'id', 'organization_id');
    }
    
}
