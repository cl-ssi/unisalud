<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\ParticipantRequired;

class Participant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'participant_id',
        'appointment_id',
        'type',
        'period_id',
        'user_id',
        'location_id',
        'required',
        'status'
    ];

    protected $casts = [
        'required' => ParticipantRequired::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
