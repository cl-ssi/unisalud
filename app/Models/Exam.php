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
        'path'
    ];

    public function patients(): BelongsTo
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

    public function biradsMam(): Builder
    {
        return $this->where('birards_mamografia', '>=' , 0);
    }

}
