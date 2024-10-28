<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitlistEvent extends Model
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
        'status', // NO DERIVADO - DERIVADO - CITADO - ATENDIDO - INASISTENTE - INCONTACTABLE - EGRESADO
        'registered_at',
        'text',
        'discharge', // ATENCION REALIZADA - RECHAZO - INASISTENCIA - FALLECIMIENTO - CONTACTO NO CORRESPONDE - ATENCION OTORGADA EN EXTRASISTEMA
        'appointment_at',
        'waitlist_id',
        'register_user_id',
    ];

    public function waitlist(): BelongsTo
    {
        return $this->belongsTo(Waitlist::class, 'waitlist_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'register_user_id');
    }

    protected $table = 'wait_events';
}
