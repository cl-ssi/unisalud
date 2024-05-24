<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commune extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code_deis',
        'latitude',
        'longitude',
        'region_id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    protected $dates = ['deleted_at'];
    
    /*
    public function agreements() 
    {
        return $this->hasMany('App\Agreement\Agreement');
    }

    
    public function samu()
    {
        return $this->belongsTo('App\Models\Samu\Commune','id', 'commune_id');
    }
    */
}
