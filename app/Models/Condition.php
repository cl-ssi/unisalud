<?php

namespace App\Models;

use App\Enums\ConditionDependency;
use Google\Protobuf\Any;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Condition extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'code', 'description', 'type', 'risk', 'parent_id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => ConditionDependency::class,
        ];
    }

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

    public function countDependents(): int
    {
        return $this->dependentUser()->where('condition_id', $this->id)->count();
    }

    public static function order(): Condition|Builder|array
    {
        $parents = self::whereNotNull('parent_id');
        if (!$parents) {
            return self::whereNotNull('id');
        }
        $ids = [];
        $passed = [];
        foreach (self::get()->sortBy('id') as $condition) {
            if (!in_array($condition->id, $passed)) {
                $passed[] = $condition->id;
                $ids[] = $condition->id;
                if (!$condition->subConditions->isEmpty()) {
                    $ids = array_merge($ids, $condition->subConditions->sortBy('id')->pluck('id')->toArray());
                    $passed = array_merge($passed, $condition->subConditions->sortBy('id')->pluck('id')->toArray());
                }
            }
        }
        return Condition::whereIn('id', $ids)->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')');
    }

    public static function orderedOptions(): Collection | array
    {
        $out = [];
        foreach(self::order()->get(['name', 'id', 'parent_id']) as $condition){
            $out[$condition->id] =  (($condition->parent_id ? '---' : '') . $condition->name->getLabel());
        }
        // return self::order()->get(['name', 'id', 'parent_id'])->map(fn($record) => [$record->id, (($record->parent_id ? '---' : '') . $record->name->getLabel())]);
        return $out;
    }

    public static function parentsOnly(): Collection
    {
        return self::whereNull('parent_id')->get();
    }

    public static function childsOnly(): Collection
    {
        return self::whereNotNull('parent_id')->get();
    }

    public static function getHeadingAttribute(string $name): string
    {
        $headings = [
            'electrodependencia' => '',
            'movilidad reducida' => '',
            'oxigeno dependiente' => '',
            'alimentacion enteral' => '',
            'oncologicos' => '',
            'cuidados paliativos universales' => '',
            'naneas' => '',
            'asistencia ventilatoria no invasiva' => '',
            'asistencia ventilatoria invasiva' => '',
            'concentradores de oxigeno' => '',
        ];

        return $headings[$name] ?? '';
    }

    protected $table = 'condition';
}
