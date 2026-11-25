<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * One of the 87 GES health problems (MINSAL), with the CIE-10 codes it covers.
 */
class GesHealthProblem extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
    ];

    protected $table = 'ges_health_problems';

    /** normalized CIE-10 code => problem ids, loaded once per process. */
    protected static ?array $codeIndex = null;

    /** 3-character category => problem ids of the subcodes in the GES list. */
    protected static ?array $categoryIndex = null;

    /** @var array<int, self>|null */
    protected static ?array $problems = null;

    /**
     * CIE-10 code as stored in ges_health_problem_codes: no dot, no †/* marks,
     * no X filler. "N18.4" → "N184", "C23X" → "C23".
     */
    public static function normalizeCode(?string $code): string
    {
        $code = strtoupper(str_replace(['.', '-', '†', '*', ' '], '', trim((string) $code)));

        return strlen($code) === 4 && str_ends_with($code, 'X') ? substr($code, 0, 3) : $code;
    }

    /**
     * GES problems covering a CIE-10 code, both ways between a category and
     * its subcodes:
     *  - a 3-character code in the GES list covers its whole category
     *    ("Y05" covers Y05.0 … Y05.9);
     *  - a 3-character code is covered by the problems of its subcodes in the
     *    list ("K80", e.g. a diagnosis written as "COLELITIASIS", is covered by
     *    problem 26 through K80.0 … K80.8).
     *
     * @return Collection<int, self>
     */
    public static function forCie10Code(?string $code): Collection
    {
        $code = static::normalizeCode($code);
        if ($code === '') {
            return new Collection();
        }

        if (static::$codeIndex === null) {
            static::$codeIndex = [];
            static::$categoryIndex = [];
            foreach (DB::table('ges_health_problem_codes')->get(['ges_health_problem_id', 'code']) as $row) {
                static::$codeIndex[$row->code][] = $row->ges_health_problem_id;
                if (strlen($row->code) > 3) {
                    static::$categoryIndex[substr($row->code, 0, 3)][] = $row->ges_health_problem_id;
                }
            }
            static::$problems = static::all()->keyBy('id')->all();
        }

        $ids = array_unique([
            ...static::$codeIndex[$code] ?? [],
            ...(strlen($code) > 3 ? static::$codeIndex[substr($code, 0, 3)] ?? [] : static::$categoryIndex[$code] ?? []),
        ]);
        sort($ids);

        return new Collection(array_map(fn ($id) => static::$problems[$id], $ids));
    }

    public function label(): string
    {
        return "{$this->id}. {$this->name}";
    }

    /**
     * Restricts a query joined to cie10 (as `$cie10Table`) to the codes of
     * the given problems, with the same category rule as forCie10Code().
     */
    public static function whereCie10InProblems($query, array $problemIds, string $cie10Table = 'cie10')
    {
        $codes = DB::table('ges_health_problem_codes')->whereIn('ges_health_problem_id', $problemIds)->pluck('code')->unique();
        $normalized = "REPLACE({$cie10Table}.code, '.', '')";

        $categoriesOfSubcodes = $codes->filter(fn ($c) => strlen($c) > 3)->map(fn ($c) => substr($c, 0, 3))->unique()->values()->all();

        return $query->where(fn ($q) => $q
            ->whereIn(DB::raw($normalized), $codes->all())
            ->orWhereIn(DB::raw("LEFT({$normalized}, 3)"), $codes->filter(fn ($c) => strlen($c) === 3)->values()->all())
            ->orWhere(fn ($q2) => $q2
                ->whereRaw("CHAR_LENGTH({$normalized}) = 3")
                ->whereIn(DB::raw($normalized), $categoriesOfSubcodes)));
    }
}
