<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\DependentUser;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Number;

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
        'code',
        'description',
        'type',
        'risk',
        'parent_id'
    ];

    public function dependentUser(): BelongsToMany
    {
        return $this->BelongsToMany(DependentUser::class, relatedPivotKey: 'dependent_user_id')
            ->withPivot('dependent_user_id', 'condition_id') // Atributos de la tabla pivote
            ->withTimestamps(); // Si tienes timestamps en la tabla pivote        
    }

    public function parent()
    {
        return $this->belongsTo(Condition::class, 'parent_id');
    }

    public function subConditions()
    {
        return $this->hasMany(Condition::class, 'parent_id');
    }

    public function countDependents() : Int
    {
        return $this->dependentUser()->where('condition_id', $this->id)->count();
    }

    protected $table = 'condition';
}
