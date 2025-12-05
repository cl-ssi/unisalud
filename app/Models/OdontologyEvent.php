<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontologyEvent extends Model
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
        'status',
        'registered_at',
        'text',
        'discharge',
        'appointment_at',
        'waitlist_id',
        'register_user_id',
    ];

    public function waitlist(): BelongsTo
    {
        return $this->belongsTo(OdontologyWaitlist::class, 'waitlist_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'register_user_id');
    }

    protected $table = 'odontology_events';
}
