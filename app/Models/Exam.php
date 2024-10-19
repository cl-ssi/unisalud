<?php

namespace App\Models;

use App\Models\Patient;
use App\Models\Establishment;
use App\Models\Commune;
use Doctrine\DBAL\Query;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Exam extends Model
{
    protected $table = 'mx_exams';
    protected $fillable = [
        'servicio_salud',
        'comuna',
        'profesional_solicita',
        'diagnostico',
        'cesfam',
        'establecimiento_realiza_examen',
        'establecimiento_deriva_examen',
        'medico',
        'fonasa',
        'date_exam_order',
        'date_exam',
        'date_exam_reception',
        'date_exam_informs',
        'derivation_reason',
        'exam_type',
        'birards_mamografia',
        'birards_ecografia',
        'birards_proyeccion',
        'observations',
        'path',
        'filename',
        'ref_order_number',
        'load_source',
        'load_id',
        'user_id',
        'patient_id',
        'sigte_id',
    ];

    public function patient(): BelongsTo
    {
        // return $this->belongsTo('\App\Models\Patient', 'patient_id', 'id');
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function commune(): HasOne
    {
        return $this->HasOne(Commune::class, 'code_deis', 'comuna'); //->Wherein('region_id',['1']);
    }

    public function establishmentOrigin(): HasOne
    {
        //->Where('id','LIKE','%'.$idRole.'%')
        return $this->HasOne(Establishment::class, 'new_code_deis', 'cesfam');

    }

    public function establishmentExam(): HasOne
    {
        //->Where('id','LIKE','%'.$idRole.'%')
        return $this->HasOne(Establishment::class, 'new_code_deis', 'establecimiento_realiza_examen');
    }

}
