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
        'description',
        'type',
        'risk'
    ];

    public function dependentUser(): BelongsToMany
    {
        return $this->BelongsToMany(DependentUser::class, relatedPivotKey: 'dependent_user_id')
            ->withPivot('dependent_user_id', 'condition_id') // Atributos de la tabla pivote
            ->withTimestamps(); // Si tienes timestamps en la tabla pivote        
    }

    public function countDependents() : Int
    {
        return $this->dependentUser()->where('condition_id', $this->id)->count();
    }

    protected $table = 'condition';
}
