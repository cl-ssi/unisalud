<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory;

    /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id','id_minsal','name'
];

use SoftDeletes;

/**
 * The attributes that should be mutated to dates.
 *
 * @var array
 */
protected $dates = ['deleted_at'];
}


