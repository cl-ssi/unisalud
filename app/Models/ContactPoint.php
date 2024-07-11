<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPoint extends Model
{
    use HasFactory;

    // FIXME: agregar todos los attributos
    protected $fillable = [
        'system',
        'value',
    ];

    // TODO: Hacer las relaciones
}
