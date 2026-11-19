<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandManagementSpeciality extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /** TIPO_PREST code whose PRESTA_MIN is a Código MAI from this catalog. */
    public const ENTRY_TYPE_CODE = '4';

    protected $fillable = [
        'id',
        'code',     // Código MAI (PRESTA_MIN when TIPO_PREST = 4)
        'text',     // Especialidad
        'name',     // Glosa
    ];

    protected $table = 'demand_management_specialities';

    /**
     * Catalog entry for a TIPO_PREST 4 PRESTA_MIN value, by exact Código MAI.
     */
    public static function forCode(?string $code): ?self
    {
        static $byCode = null;

        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        $byCode ??= self::query()->whereNotNull('code')->get()->keyBy(fn (self $s) => trim($s->code));

        return $byCode[$code] ?? null;
    }

    public function waitlists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DemandManagementWaitlist::class, 'specialty_id');
    }

    /** "Código MAI Glosa" for coded rows; the specialty text otherwise. */
    public function label(): string
    {
        return filled($this->code) ? trim("{$this->code} {$this->name}") : (string) $this->text;
    }
}
