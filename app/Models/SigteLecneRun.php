<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A RUN that has appeared in an uploaded "LE CNE" file. */
class SigteLecneRun extends Model
{
    protected $fillable = [
        'run',
    ];
}
