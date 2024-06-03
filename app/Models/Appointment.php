<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
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
        'appointment_id',
        'status',
        'cod_con_cancel_reason_id',
        'cod_con_appointment_type_id',
        'priority',
        'description',
        'start',
        'end',
        'created',
        'comment',
        'patient_instruction',
        'mp_prog_prop_detail_id',
        'mp_theoretical_programming_id',
        'sent_to_hetg_at'
    ];

    protected $casts = [
        'status'  => AppointmentStatus::class
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function appointmentType(): BelongsTo
    {
        return $this->BelongsTo(CodConAppointmentType::class, 'cod_con_appointment_type_id');
    }
}
