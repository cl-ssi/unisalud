<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

use App\Observers\DependentUserObserver;

use App\Models\DependentCaregiver;
use App\Models\Condition;
use App\Models\User;

use App\Enums\ConditionClinicalStatus;
use App\Enums\ConditionVerificationStatus;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy([DependentUserObserver::class])]
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
        'tech_aid',
        'tech_aid_date',
        'nutrition_assistance',
        'nutrition_assistance_date',
        'diapers_size',
        'nasogastric_catheter',
        'urinary_catheter',
        'extra_info',
        'risks',
    ];

    protected $casts = [
        'cod_con_clinical_status'       => ConditionClinicalStatus::class,
        'cod_con_verification_status'   => ConditionVerificationStatus::class,
        'risks' => 'array',
        'badges' => 'array',
        'pneumonia' => 'date',
        'influenza' => 'date',
        'covid-19'  => 'date',
    ];

    protected $appends = [
        'badges'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conditions(): BelongsToMany
    {
        return $this->belongsToMany(Condition::class, foreignPivotKey: 'dependent_user_id')
            ->withPivot('dependent_user_id', 'condition_id') // Atributos de la tabla pivote
            ->withTimestamps(); // Si tienes timestamps en la tabla pivote
    }

    public function dependentCaregiver(): HasOne
    {
        return $this->HasOne(DependentCaregiver::class);
    }

    public function getBadgesAttribute(): array
    {
        $out = [];
        $data = [
            'barthel'           => true,
            'empam'             => false,
            'eleam'             => false,
            'upp'               => false,
            'elaborated_plan'   => false,
            'evaluated_plan'    => false,
            'pneumonia'         => true,
            'influenza'         => true,
            'covid-19'          => true,
        ];
        foreach ($data as $name => $show) {
            if ($this->$name) {
                $value = (strtotime($this->$name) !== false) ? ($this->$name->format('d/m/Y')) : $this->$name;
                $out[] = $this->getLabel($name) . (($show) ? (': ' . $value) : '');
            }
        }
        return $out;
    }

    public function getLabel(string $name): string
    {
        $headings = [
            'diagnosis' => 'Diagnostico',
            'check_in_date' => 'Fecha de Ingreso',
            'check_out_date' => 'Fecha de Egreso',
            'integral_visits' => 'Vistas Integrales',
            'treatment_visits' => 'Visitas de Tratamiento',
            'last_integral_visit' => 'Última Visita Integral',
            'last_treatment_visit' => 'Última Visita de Tratamiento',
            'barthel' => 'Barthel',
            'empam' => 'Emp / Empam',
            'eleam' => 'Eleam',
            'upp' => 'UPP',
            'elaborated_plan' => 'Plan Elaborado',
            'evaluated_plan' => 'Plan Evaluado',
            'pneumonia' => 'Neumonia',
            'influenza' => 'Influenza',
            'covid_19' => 'Covid-19',
            'tech_aid' => 'Ayuda Técnica',
            'tech_aid_date' => 'Fecha Ayuda Técnica',
            'nutrition_assistance' => 'Entrega de Alimentación',
            'nutrition_assistance_date' => 'Fecha Entrega de Alimentación',
            'diapers_size' => 'Tamaño de Pañal',
            'nasogastric_catheter' => 'Sonda Nasogástrica',
            'urinary_catheter' => 'Sonda Urinaria',
            'extra_info' => 'Otros',
            'risks' => 'Zonas de Riesgo',
        ];
        return $headings[$name] ?? '';
    }

    protected $table = 'dependent_user';
}
