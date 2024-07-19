<?php

namespace App\Models;

use App\Models\Patient;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Exam extends Model
{
    protected $table = 'mx_exams';
    protected $fillable = [
        'path'
    ];

    public function patients(): BelongsTo
    {
        // return $this->belongsTo('\App\Models\Patient', 'patient_id', 'id');
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function establishmentOrigin(): HasOne
    {
        return $this->HasOne(Establishment::class, 'new_code_deis', 'cesfam');
    }

    public function establishmentExam(): HasOne
    {
        return $this->HasOne(Establishment::class, 'new_code_deis', 'establecimiento_realiza_examen');
    }
}
