<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitlistContact extends Model
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
        'type',
        'status', // SI - NO
        'contacted_at',
        'text',
        'waitlist_id',
        'register_user_id',
        'organization_user_id'
    ];

    public function waitlist()
    {
        return $this->belongsTo(Waitlist::class, 'waitlist_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'register_user_id');
    }

    protected $table = 'wait_contacts';
}
