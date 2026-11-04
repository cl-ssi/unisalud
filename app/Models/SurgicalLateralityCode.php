<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A surgery that SIGTE requires to be reported with PLANO and, for some,
 * EXTREMIDAD ("Código cirugías con lateralidad", MINSAL).
 */
class SurgicalLateralityCode extends Model
{
    /**
     * PLANO codes (RNLE codification).
     */
    public const PLANO_LABELS = [
        '1' => 'Izquierdo',
        '2' => 'Derecho',
        '3' => 'Superior',
        '4' => 'Inferior',
        '5' => 'Medio',
    ];

    /**
     * EXTREMIDAD codes (RNLE codification).
     */
    public const EXTREMITY_LABELS = [
        '1'  => 'Dedo',
        '2'  => 'Mano',
        '3'  => 'Brazo',
        '4'  => 'Codo',
        '5'  => 'Muñeca',
        '6'  => 'Antebrazo',
        '7'  => 'Hombro',
        '8'  => 'Pie',
        '9'  => 'Tobillo',
        '10' => 'Rodilla',
        '11' => 'Muslo',
        '12' => 'Sacroilíaca',
        '13' => 'Cadera',
        '14' => 'Pierna',
        '15' => 'Acromio-clavicular',
        '16' => 'Esterno-clavicular',
        '17' => 'Cúbito',
        '18' => 'Radio',
        '19' => 'Cervical',
        '20' => 'Dorsal',
        '21' => 'Lumbar',
        '22' => 'Párpado superior',
        '23' => 'Párpado inferior',
        '24' => 'Pulgar',
        '25' => 'Columna',
    ];

    protected $fillable = [
        'code',
        'text',
        'group',
        'planes',
        'extremities',
    ];

    protected $casts = [
        'planes'      => 'array',
        'extremities' => 'array',
    ];

    protected $table = 'surgical_laterality_codes';

    public static function forCode(?string $code): ?self
    {
        return filled($code) ? static::where('code', trim($code))->first() : null;
    }

    /**
     * @return array<string, string> allowed PLANO code => label
     */
    public function planeOptions(): array
    {
        return collect($this->planes)->mapWithKeys(fn ($c) => [(string) $c => self::PLANO_LABELS[$c] ?? $c])->all();
    }

    /**
     * @return array<string, string> allowed EXTREMIDAD code => label (empty when it doesn't apply)
     */
    public function extremityOptions(): array
    {
        return collect($this->extremities)->mapWithKeys(fn ($c) => [(string) $c => self::EXTREMITY_LABELS[$c] ?? $c])->all();
    }
}
