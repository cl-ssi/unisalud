<?php

namespace App\Models\Epi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SuspectCase extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'type',
        //datos chagas
        'research_group',
        'chagas_result_screening',
        'chagas_result_screening_at',
        'chagas_result_confirmation',
        'chagas_result_confirmation_at',
        'newborn_week',
        //fin datos chagas

        'age', 'gender', 'sample_at', 'epidemiological_week',
        'origin', 'run_medic', 'symptoms', 'symptoms_at',
        'reception_at', 'receptor_id',
        'result_ifd_at', 'result_ifd', 'subtype',
        'pcr_sars_cov_2_at', 'pcr_sars_cov_2', 'sample_type', 'validator_id',
        'sent_external_lab_at', 'external_laboratory', 'paho_flu', 'epivigila',
        'gestation', 'gestation_week', 'close_contact', 'functionary',
        'notification_at', 'notification_mechanism',
        'discharged_at',
        'observation', 'minsal_ws_id','case_type', 'positive_condition',
        'patient_id', 'laboratory_id', 'establishment_id', 'organization_id',
        'user_id','mother_id',
        'chagas_result_screening_file',
        'chagas_result_confirmation_file',


        //Datos Examen Directo o 
        'direct_exam_at',
        'direct_exam_result',
        'direct_exam_file',

        //datos primera PCR
        'pcr_first_at',
        'pcr_first_result',
        'pcr_first_file',

        //datos segunda PCR
        'pcr_second_at',
        'pcr_second_result',
        'pcr_second_file',


        //datos tercera PCR
        'pcr_third_at',
        'pcr_third_result',
        'pcr_third_file',

        //datos del creador
        'creator_id',





        
    ];

    protected $dates = [
        'sample_at', 'symptoms_at', 'reception_at', 'result_ifd_at', 'pcr_sars_cov_2_at', 'sent_external_lab_at',
        'notification_at', 'discharged_at', 'deleted_at','chagas_result_confirmation_at','chagas_result_screening_at', 
        'direct_exam_at',
        'pcr_first_at',
        'pcr_second_at',
        'pcr_third_at',
    ];


    public function organization() {
        return $this->belongsTo('App\Models\Organization');
    }

    public function patient() {
        return $this->belongsTo('App\Models\User');
    }

    public function mother() {
        return $this->belongsTo('App\Models\User');
    }

    public function creator() {
        return $this->belongsTo('App\Models\User');
    }

    public function tracings()
    {
        return $this->hasMany('App\Models\Epi\Tracing');
    }

    protected $table = 'epi_suspect_cases';



}
