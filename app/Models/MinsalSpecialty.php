<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinsalSpecialty extends Model
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
        'code',
        'old_code',     // Presta_Min before the 07-xxx / 09-xxx codification
        'text',
        'min_age',      // years, inclusive
        'max_age',
    ];

    protected $casts = [
        'min_age' => 'float',
        'max_age' => 'float',
    ];

    protected $table = 'minsal_specialties';

    /** All specialties, loaded once per process for the imports. */
    protected static ?Collection $catalog = null;

    /**
     * Finds the specialty for a PRESTA_MIN value in either codification:
     * the current one (07-044) or the old one (07-105-0). An old code shared
     * by several specialties (e.g. adult and pediatric) is resolved by the
     * patient's age; when that still leaves more than one, the narrowest
     * range wins and a warning explains the choice.
     *
     * @return array{0: ?self, 1: ?string} [specialty, warning]
     */
    public static function resolve(?string $code, ?float $age): array
    {
        $code = strtolower(trim((string) $code));
        if ($code === '') {
            return [null, null];
        }

        static::$catalog ??= static::all();

        if ($specialty = static::$catalog->first(fn (self $s) => strtolower(trim((string) $s->code)) === $code)) {
            return [$specialty, null];
        }

        $candidates = static::$catalog->filter(fn (self $s) => strtolower(trim((string) $s->old_code)) === $code)->values();
        if ($candidates->count() <= 1) {
            return [$candidates->first(), null];
        }

        $fitting = $age === null ? $candidates : $candidates->filter(fn (self $s) => $s->allowsAge($age));
        $chosen = ($fitting->isNotEmpty() ? $fitting : $candidates)
            ->sortBy(fn (self $s) => [($s->max_age ?? 150) - ($s->min_age ?? 0), $s->code])
            ->first();

        $warning = $fitting->count() === 1 ? null
            : "Código antiguo {$code} corresponde a " . $candidates->map(fn (self $s) => "{$s->code} {$s->text}")->implode(', ')
                . ' y la edad del paciente no permite elegir una; use el código nuevo.';

        return [$chosen, $warning];
    }

    public static function ageInYears(?string $birthday, ?string $at = null): ?float
    {
        if (blank($birthday)) {
            return null;
        }

        return Carbon::parse($birthday)->diffInDays(Carbon::parse($at ?? now()), false) / 365.25;
    }

    public function allowsAge(float $age): bool
    {
        // A whole-year maximum covers that whole year: at 15 years and 11
        // months the patient is still "15 años" for a 0 – 15 range.
        $max = $this->max_age === null ? null
            : (floor($this->max_age) == $this->max_age ? $this->max_age + 1 : $this->max_age);

        return ($this->min_age === null || $age >= $this->min_age)
            && ($max === null || $age < $max || $age == $this->max_age);
    }

    /**
     * Warning text when the patient's age is outside the range SIGTE accepts
     * for this specialty; null when it fits or isn't known.
     */
    public function ageWarning(?float $age): ?string
    {
        if ($age === null || $this->allowsAge($age)) {
            return null;
        }

        $range = fn (?float $years) => match (true) {
            $years === null => '',
            $years == 0     => '0',
            $years < 1      => round($years * 365.25) . ' días',
            default         => (+$years) . ' años',
        };

        return sprintf(
            'Edad %s fuera del rango de %s %s (%s – %s).',
            $age < 1 ? round($age * 365.25) . ' días' : floor($age) . ' años',
            $this->code,
            $this->text,
            $range($this->min_age) ?: '0',
            $range($this->max_age) ?: 'sin límite',
        );
    }
}
