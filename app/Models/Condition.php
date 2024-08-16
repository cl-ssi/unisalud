<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\ConditionClinicalStatus;
use App\Enums\ConditionVerificationStatus;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Condition extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'identifier',
        'cod_con_clinical_status',
        'cod_con_verification_status',
        'cod_con_code_id',
        'user_id',
        'user_condition',
        'diagnosis',
        'check_in_date',
        'check_out_date',
        'integral_visits',
        'treatment_visits',
        'last_integral_visit',
        'last_treatment_visit',
        'barthel',
        'empam',
        'eleam',
        'upp',
        'elaborated_plan',
        'evaluated_plan',
        'pneumonia',
        'influenza',
        'covid_19',
        'extra_info',
        'tech_aid',
        'nutrition_assistance',
        'flood_zone',
    ];

    protected $casts = [
        'cod_con_clinical_status'       => ConditionClinicalStatus::class,
        'cod_con_verification_status'   => ConditionVerificationStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coding(): BelongsTo
    {
        return $this->belongsTo(Coding::class, 'cod_con_code_id');
    }

    protected $table = 'conditions';
}
