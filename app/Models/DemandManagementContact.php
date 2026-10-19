<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandManagementContact extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'demand_management_contacts';

    protected $fillable = [
        'id',
        'type',
        'status',
        'contacted_at',
        'text',
        'waitlist_id',
        'register_user_id',
        'organization_user_id',
    ];

    public function waitlist(): BelongsTo
    {
        return $this->belongsTo(DemandManagementWaitlist::class, 'waitlist_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'register_user_id');
    }
}
