<?php

namespace App\Models\Samu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Samu\Shift;
use App\Models\Samu\Event;
use App\Models\Samu\MobileInService;
use App\Models\User;

class Mobile extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = "samu_mobiles";

    protected $fillable = [
        'id',
        'code',
        'name',
        'plate',
        'type_id',
        'description',
        'status',
        'managed'
    ];

    protected $appends = [
        'last_location'
    ];

    public function locations()
    {
        return $this->hasOne(Gps::class, 'mobile_id')->latest();
    }
    
    public function mobileInServices()
    {
        return $this->hasMany(MobileInService::class);
    }

    public function type()
    {
        return $this->belongsTo(MobileType::class,'type_id');
    }
    
    public function getLastLocationAttribute()
    {
        if($this->locations())
        {
            return $this->locations;
        }
        return null;
    }
}
