<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\DependentUser;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DependentCaregiver extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dependent_caregiver';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'id',
        'dependent_user_id',
        'user_id',
        'relative',
        'empam',
        'zarit',
        'immunizations',
        'elaborated_plan',
        'evaluated_plan',
        'trained',
        'stipend',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dependentUser(): HasOne
    {
        return $this->hasOne(DependentUser::class);
    }
}
