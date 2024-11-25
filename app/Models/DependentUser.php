<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\DependentConditions;
use App\Models\DependentCaregiver;
use App\Models\User;

use App\Enums\ConditionClinicalStatus;
use App\Enums\ConditionVerificationStatus;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class DependentUser extends Model
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
        'covid_19_date',
        'extra_info',
        'tech_aid',
        'tech_aid_date',
        'nutrition_assistance',
        'nutrition_assistance_date',
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

    public function dependentConditions(): HasMany
    {
        return $this->hasMany(DependentConditions::class);
    }

    // public function conditions(): hasMany
    // {
    //     // return $this->hasManyThrough(Condition::class, DependentConditions::class, 'dependent_user_id', 'condition_id');
    //     return $this->hasMany(Condition::class)->using(DependentConditions::class);
    // }

    public function dependentCaregiver():HasOne
    {
        return $this->HasOne(DependentCaregiver::class);
    }

    protected $table = 'dependent_user';
}
