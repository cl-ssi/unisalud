<?php

namespace App\Models\Samu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Samu\Shift;
use App\Models\Samu\Mobile;
use App\Models\Samu\MobileCrew;
use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\User;

class MobileInService extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    use SoftDeletes;

    protected $table="samu_mobiles_in_service";

    protected $fillable = [
        'id',
        'shift_id',
        'mobile_id',
        'type_id',
        'position',
        'o2',
        'lunch_start_at',
        'lunch_end_at',
        'observation',
        'status',
    ];

    protected $appends = [
        'last_event'
    ];

    /**
    * The attributes that should be mutated to dates.
    *
    * @var array
    */
    protected $dates = [
        'lunch_start_at',
        'lunch_break_start_at',
        'lunch_break_end_at',
        'lunch_end_at',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function mobile()
    {
        /* HasOne? */
        return $this->belongsTo(Mobile::class);
    }

    public function type()
    {
        return $this->belongsTo(MobileType::class,'type_id');
    }

    public function crew()
    {
        // $mobileCrews = MobileCrew::where('mobiles_in_service_id',$this->id)
        //                 ->with('user')
        //                 ->get();
        // $users = collect();
        // foreach($mobileCrews as $mc) {
        //     $users->add($mc->user);
        // }
        // return $users;
        return $this->belongsToMany(User::class,'samu_mobile_crew','mobiles_in_service_id')
                    //->join('amenity_master','amenity_icon_url','=','image_url')
                    ->using(MobileCrew::class)
                    ->withPivot('id','job_type_id','assumes_at','leaves_at')
                    ->withTimestamps();
    }

    public function currentCrew()
    {
        return $this->belongsToMany(User::class,'samu_mobile_crew','mobiles_in_service_id')
            ->using(MobileCrew::class)
            ->withPivot('id','job_type_id','assumes_at','leaves_at')
            ->where(function($query) {
                $query->where('mobiles_in_service_id', $this->id)
                    ->where('assumes_at', '<', now())
                    ->where('leaves_at', '>', now());
            })
            ->orWhere(function($query) {
                $query->where('mobiles_in_service_id', $this->id)
                    ->where('assumes_at', '<', now())
                    ->where('leaves_at', '=', null);
            })
            ->withTimestamps();
    }

    public function follows()
    {
        return $this->belongsToMany(Follow::class,'samu_follow_mis');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function getTotalMinutesAttribute()
    {
        $total = 0;
        if($this->lunch_start_at && $this->lunch_break_start_at && $this->lunch_break_end_at && $this->lunch_end_at)
        {
            $firstBreak = intval($this->lunch_start_at->diff($this->lunch_break_start_at)->format('%I'));
            $secondBreak = intval($this->lunch_break_end_at->diff($this->lunch_end_at)->format('%I'));
            $total = $firstBreak + $secondBreak;
        }
        elseif($this->lunch_start_at && $this->lunch_end_at)
        {
            $total = intval($this->lunch_start_at->diff($this->lunch_end_at)->format('%I'));
        }
        return $total;
    }

    public function getLastEventAttribute()
    {
        return $this->events->where('status', true)->last();
    }

    public function getEventStatusAttribute()
    {
        if($this->status)
        {
            $lastEvent = $this->events->where('status',true)->last();
            if(!$lastEvent) return "Disponible";
            else
            {
                $msg = 'Aviso de salida';
                if(!$lastEvent->departure_at)           $msg = 'Aviso de salida';
                if($lastEvent->mobile_departure_at)     $msg = 'Navegación';
                if($lastEvent->mobile_arrival_at)       $msg = 'Contacto';
                if($lastEvent->route_to_healtcenter_at) $msg = 'Navegación';
                if($lastEvent->healthcenter_at)         $msg = 'AP';
                if($lastEvent->return_base_at)          $msg = 'Retorno a base';
                if($lastEvent->on_base_at)              $msg = 'Disponible';
                return $msg;
            }
        }
        else
        {
            return "Inactivo";
        }
    }

    public function isHavingLunch()
    {
        return $this->lunch_start_at != null && $this->lunch_end_at == null;
    }

    public static function reorder(Shift $shift)
    {
        $mobilesInService = MobileInService::query()
            ->whereShiftId($shift->id)
            ->orderBy('status', 'DESC')
            ->orderBy('position', 'ASC')
            ->get();

        foreach($mobilesInService as $index => $mis)
        {
            $mis->update([
                'position' => $index + 1
            ]);
        }
    }
}
