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

    protected $table = 'locations';
}