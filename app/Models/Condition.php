<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\DependentUser;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Condition extends Model
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
        'name',
        'description',
        'type',
        'risk'
    ];

    public function dependentUser(): BelongsToMany
    {
        return $this->BelongsToMany(DependentUser::class, relatedPivotKey: 'dependent_user_id')->withTimestamps();
    }

    protected $table = 'condition';
}
