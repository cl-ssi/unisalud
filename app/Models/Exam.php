<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $table = 'mx_exams';
    protected $fillable = [
        'path'
    ];

    public function patients() {
        return $this->belongsTo('\App\Models\Patient', 'patient_id', 'id');
    }
}
