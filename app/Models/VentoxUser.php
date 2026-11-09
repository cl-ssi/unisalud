<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\VentoxUserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([VentoxUserObserver::class])]
class VentoxUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ventox_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'health_service_check',
        'medical_center_control',
        'equipment_type',
        'brand',
        'model',
        'therapy_time',
        'flow',
        'risks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'risks' => 'array',
        'controls' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'controls',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getControlsAttribute(): array
    {
        // Mirrored from DependentUser pattern; VentoxUser has no health control flags (barthel, empam, etc.),
        // so this accessor returns an array representation of 'risks' if present, or an empty array.
        $out = [];
        if (!empty($this->risks) && is_array($this->risks)) {
            foreach ($this->risks as $risk) {
                $out[] = (string) $risk;
            }
        }

        return $out;
    }

    public static function getLabel(string $name): string
    {
        $headings = [
            'health_service_check'   => 'Servicio Salud Control',
            'medical_center_control' => 'Hospital Control',
            'equipment_type'         => 'Tipo de equipo',
            'brand'                  => 'Marca',
            'model'                  => 'Modelo',
            'therapy_time'           => 'Tiempo de terapia (minutos)',
            'flow'                   => 'Flujo',
            'risks'                  => 'Zonas de Riesgo',
        ];

        return $headings[$name] ?? '';
    }
}
