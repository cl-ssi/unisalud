<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SIGTE procedure catalog: the PRESTA_MIN / PRESTA_MIN_SALIDA codes of
 * TIPO_PREST = 3 (Procedimiento) rows, e.g. "13-01-021" Audiometría en Adulto.
 */
class SigteProcedure extends Model
{
    /** TIPO_PREST code whose PRESTA_MIN is a procedure from this catalog. */
    public const ENTRY_TYPE_CODE = '3';

    protected $fillable = [
        'code',
        'name',
        'type_code',
        'type_name',
    ];

    /**
     * Procedure for a PRESTA_MIN value, by exact code.
     */
    public static function forCode(?string $code): ?self
    {
        static $byCode = null;

        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        $byCode ??= self::all()->keyBy('code');

        return $byCode[$code] ?? null;
    }

    public function label(): string
    {
        return "{$this->code} {$this->name}";
    }
}
