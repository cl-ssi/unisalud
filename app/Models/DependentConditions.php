<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\DependentUser;
use App\Models\Condition;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DependentConditions extends Model
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
        'dependent_user_id',
        'condition_id'
    ];

    public function dependentUser(): BelongsTo
    {
        return $this->belongsTo(DependentUser::class);
    }

    public function condition(): BelongsTo
    {
        return $this->BelongsTo(Condition::class);
    }

    protected $table = 'dependent_conditions';

}
