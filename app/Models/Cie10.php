<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cie10 extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name'
    ];

    protected $table = 'cie10';
}
