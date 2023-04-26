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
        'suspect_case_id',
        'patient_id',

        //Resultado
        'result_at',
        'result_observation',

        //3 Entrega de Resultado
        'delivery_of_result_1',
        'mechanism_of_result_1',
        'observation_of_result_1',
        'delivery_of_result_2',
        'mechanism_of_result_2',
        'observation_of_result_2',
        'delivery_of_result_3',
        'mechanism_of_result_3',
        'observation_of_result_3',
        'delivery_of_result_4',
        'mechanism_of_result_4',
        'observation_of_result_4',

        //Interconsulta
        'date_of_sic',
        'polyclinic_sic',

        //Notificación
        'date_of_notification',
        'epi_notification',
        'cie10name_notification',

        //Seguimiento
        'index',
        'next_control_at',
        'status',        
        'establishment_id',
        'date_of_last_birth',
        'occupation',
        'responsible_family_member',
        'allergies',
        'common_use_drugs',
        'morbid_history',
        'family_history',
        'indications',
        'observations',

    ];

    protected $dates = [        
        'next_control_at',
    ];


    

}
