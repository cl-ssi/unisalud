<?php

namespace App\Models\Samu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Samu\Shift;
use App\Models\Samu\Event;
use App\Models\Samu\Ot;
use App\Models\User;
use App\Models\Commune;


class Call extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    use SoftDeletes;

    protected $table="samu_calls";

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'shift_id',
        'call_id',
        'commune_id',
        'classification',
        'clasificator_id',
        'key_id',
        'regulator_id',
        'receptor_id',
        'hour',
        'reason',
        'police_intervention',
        'information',
        'regulation',
        'bls',
        'sex',
        'applicant',
        'age',
        'year',
        'month',
        'address',
        'address_reference',
        'latitude',
        'longitude',
        'telephone',
    ];

    /**
    * The attributes that should be mutated to dates.
    *
    * @var array
    */
    protected $dates = [
        'hour','bls'
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function receptor()
    {
        return $this->belongsTo(User::class,'receptor_id');
    }

    public function regulator()
    {
        return $this->belongsTo(User::class,'regulator_id');

    }

    /* Una llamada puede hace referencia o tener relación con otra llamada  */
    public function referenceCall()
    {
        return $this->belongsTo(Call::class,'call_id');
    }

    /* Una llamada puede tener muchas llamadas asociadas  */
    public function associatedCalls()
    {
        return $this->hasMany(Call::class);
    }

    public function getSexAbbrAttribute()
    {
        switch($this->sex)
        {
            case 'MALE': return 'MASC'; break;
            case 'FEMALE': return 'FEM'; break;
            case 'UNKNOWN': return 'DESC'; break;
            case 'OTHER': return 'OTRO'; break;
        }
    }

    public function getAgeFormatAttribute()
    {
        $age = '';
        if($this->year != 0)
        {
            $age .= ($this->year) . ($this->year == 1 ?  ' AÑO ' : ' AÑOS ');
        }
        if($this->month)
        {
            $age .= ($this->month) . ($this->month == 1 ?  ' MES' : ' MESES');
        }
        return $age;
    }

    public function getMonthFormatAttribute()
    {
        // TODO: Eliminar a futuro, no se elimina de inmediato ya que es necesaria para migración
        $month = null;
        if($this->age)
        {
            list($year, $month) = explode('.', $this->age);
            $month = ($month == 0) ? null : (int)$month;
        }
        return $month;
    }

    public function getFullAddressAttribute()
    {
        $full_address = $this->address;
        if($this->address_reference)
            $full_address = "$this->address ($this->address_reference)";
        return $full_address;
    }

    public function scopeWithClassification($query, $type)
    {
        return $query->whereIn('classification', $type);
    }
}
