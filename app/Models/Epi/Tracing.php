<?php

namespace App\Models\Epi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tracing extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'epi_tracings';

    protected $fillable = [
    ];

}
