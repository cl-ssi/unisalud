<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Establishment extends Model
{
    //use SoftDeletes;

    protected $table = 'mx_establishments';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        // 'id','name','type','deis','commune_id'
        'id','name','alias','type','old_code_deis','new_code_deis','service','dependency',//'comuna','commune_code_deis',
        'commune_id'
    ];

    public function commune() {
        return $this->belongsTo('\App\Models\Commune');
    }

    /*public function suspectCases() {
        return $this->hasMany('App\SuspectCase');
    }*/

    /**
    * The user that belong to the establishment.
    */
    public function users() {
        return $this->belongsToMany('App\Models\User');
    }
}
