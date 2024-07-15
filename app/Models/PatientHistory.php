<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Exam;

class PatientHistory extends Model
{
    use \Sushi\Sushi;

    public $rows = [];

    public function getRows()
    {
        return $this->searchPatientHistory();
    }

    public static function searchPatientHistory()
    {
        return Exam::select(
            'mx_patients.run',
            'mx_patients.dv',
            'mx_patients.name',
            'mx_patients.fathers_family',
            'mx_patients.mothers_family',
            'mx_patients.gender',
            'mx_patients.telephone',
            'mx_patients.birthday',
            'mx_patients.address',
            'mx_exams.date_exam_order',
            'mx_exams.date_exam',
            'mx_exams.date_exam_reception',
            'mx_exams.birards_mamografia',
            'mx_exams.birards_ecografia',
            'mx_exams.birards_proyeccion',
            'mx_exams.diagnostico',
            'mx_exams.profesional_solicita',
            'mx_exams.medico',
            'mx_exams.servicio_salud',
            'communes.name',
            'mx_establishments.alias'
        )
        ->leftjoin('mx_patients', 'mx_exams.patient_id', 'mx_patients.id')
        ->leftjoin('communes', 'mx_exams.comuna', 'communes.code_deis')
        ->leftjoin('mx_establishments', 'mx_exams.cesfam', 'mx_establishments.new_code_deis')
        // ->where('mx_patients.run', '=', $run)
        // ->where('mx_exams.establecimiento_realiza_examen', '=', $this->code_deis)
        // ->where('mx_exams.cesfam', '=', $this->code_deis_request)
        // ->where('mx_exams.comuna', '=', $this->commune)
        ->get()
        ->toArray();

    }
}
