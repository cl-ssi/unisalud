<?php

namespace App\Filament\Resources\DemandManagementWaitlistResource\Pages;

use App\Enums\Sex;
use App\Filament\Resources\DemandManagementWaitlistResource;
use App\Models\DemandManagementWaitlist;
use App\Models\GesHealthProblem;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class DemandManagementStats extends Page
{
    protected static string $resource = DemandManagementWaitlistResource::class;

    protected static string $view = 'filament.resources.demand-management-waitlist-resource.pages.demand-management-stats';

    /** 👇 ESTA PROPIEDAD ES OBLIGATORIA */
    public array $stats = [];

    public array $chartStats = [];

    public array $estadoBreakdown = [];

    /** Open / closed, GES and waiting time figures. */
    public array $waitlistStats = [];

    private const ESTADO_LABELS = [
        'primer llamado'         => 'Primer Llamado',
        'segundo llamado'        => 'Segundo Llamado',
        'tercer llamado'         => 'Tercer Llamado',
        'en visita domiciliaria' => 'En Visita Domiciliaria',
        'citado'                 => 'Citado',
        'atendido praps'         => 'Atendido PRAPS',
        'atendido hetg'          => 'Atendido HETG',
        'atendido sst'           => 'Atendido SST',
        'atendido hah'           => 'Atendido HAH',
        'fallecido'              => 'Fallecido',
        'egresado'               => 'Egresado',
    ];

    public function mount(): void
{
    $this->stats = [
        'total'          => DemandManagementWaitlist::count(),
        'primer_llamado' => DemandManagementWaitlist::where('status', 'primer llamado')->count(),
        'segundo_llamado'=> DemandManagementWaitlist::where('status', 'segundo llamado')->count(),
        'citado'         => DemandManagementWaitlist::where('status', 'citado')->count(),
        'atendido_praps' => DemandManagementWaitlist::where('status', 'atendido praps')->count(),
        'atendido_hetg_sst_hah' => DemandManagementWaitlist::whereIn('status', [
            'atendido hetg', 'atendido sst', 'atendido hah'
        ])->count(),
        'fallecidos'     => DemandManagementWaitlist::where('status', 'fallecido')->count(),
        'egresados'      => DemandManagementWaitlist::where('status', 'egresado')->count(),
        'sin_estado'     => DemandManagementWaitlist::whereNull('status')->orWhere('status', '')->count(),
    ];

    $total = max($this->stats['total'], 1);

    $this->estadoBreakdown = DemandManagementWaitlist::selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->orderByDesc('total')
        ->get()
        ->map(fn ($row) => [
            'estado'     => self::ESTADO_LABELS[$row->status] ?? ($row->status ? ucfirst($row->status) : 'Sin Estado'),
            'cantidad'   => $row->total,
            'porcentaje' => round(($row->total / $total) * 100, 1),
        ])
        ->all();

    // Estadísticas adicionales para gráficos
    $this->chartStats = [
        'por_estado' => collect($this->estadoBreakdown)->pluck('cantidad', 'estado'),

        // ESTAB_DEST (SIGTE files don't carry the app's ESTABLECIMIENTO column).
        'por_establecimiento' => DemandManagementWaitlist::query()
                        ->leftJoin('organizations as dest', 'dest.id', '=', DB::raw('COALESCE(demand_management_waitlists.destiny_establishment_id, demand_management_waitlists.establishment_id)'))
                        ->selectRaw("COALESCE(dest.alias, 'Sin Establecimiento') as label, COUNT(*) as total")
                        ->groupBy('label')
                        ->orderByDesc('total')
                        ->pluck('total', 'label'),

        // From PRESTA_MIN by TIPO_PREST: MINSAL specialty (1), procedure type
        // (3), Código MAI specialty (4); else the free-text ESPECIALIDAD.
        'por_especialidad' => DemandManagementWaitlist::query()
                        ->leftJoin('minsal_specialties as ms', 'ms.id', '=', 'demand_management_waitlists.minsal_specialty_id')
                        ->leftJoin('sigte_procedures as sp', 'sp.id', '=', 'demand_management_waitlists.sigte_procedure_id')
                        ->leftJoin('demand_management_specialities as ds', 'ds.id', '=', 'demand_management_waitlists.specialty_id')
                        ->selectRaw("COALESCE(ms.text, sp.type_name, CASE WHEN ds.code IS NOT NULL THEN ds.text END, NULLIF(ds.text, 'sin especialidad'), 'Sin Especialidad') as label, COUNT(*) as total")
                        ->groupBy('label')
                        ->orderByDesc('total')
                        ->limit(15)
                        ->pluck('total', 'label'),

        // Current age, from the patient's birth date.
        'por_edad' => $this->countByBuckets(
            DemandManagementWaitlist::query()->join('users', 'users.id', '=', 'demand_management_waitlists.user_id'),
            'TIMESTAMPDIFF(YEAR, users.birthday, CURDATE())',
            [[0, 14, '0 – 14'], [15, 19, '15 – 19'], [20, 44, '20 – 44'], [45, 64, '45 – 64'], [65, 79, '65 – 79'], [80, null, '80 y más']],
            'Sin fecha de nacimiento',
        ),

        'por_sexo' => DemandManagementWaitlist::query()
                        ->join('users', 'users.id', '=', 'demand_management_waitlists.user_id')
                        ->selectRaw('users.sex as sex, COUNT(*) as total')
                        ->groupBy('users.sex')
                        ->orderByDesc('total')
                        ->get()
                        ->mapWithKeys(fn ($row) => [(Sex::tryFrom((string) $row->sex)?->getLabel() ?? 'No informado') => $row->total]),

        // Open cases only: days since F_ENTRADA.
        'por_tiempo_espera' => $this->countByBuckets(
            DemandManagementWaitlist::query()->open(),
            'DATEDIFF(CURDATE(), demand_management_waitlists.entry_date)',
            [[0, 89, 'Menos de 90 días'], [90, 179, '90 – 179 días'], [180, 364, '180 – 364 días'], [365, 729, '1 – 2 años'], [730, null, 'Más de 2 años']],
            'Sin fecha de entrada',
        ),

        'por_tipo_prestacion' => $this->countByLabel('waitlist_entry_types', 'waitlist_entry_type_id', 'text', 'Sin tipo'),

        'por_prevision' => $this->countByLabel('healthcare_types', 'healthcare_type_id', 'text', 'Sin previsión'),

        'por_causal_egreso' => DemandManagementWaitlist::query()
                        ->discharged()
                        ->selectRaw('TRIM(exit_code) as code, COUNT(*) as total')
                        ->groupBy('code')
                        ->orderByDesc('total')
                        ->get()
                        ->mapWithKeys(fn ($row) => [
                            $row->code . ' · ' . (DemandManagementWaitlist::EXIT_CODE_LABELS[ltrim($row->code, '0') ?: '0'] ?? 'Otro') => $row->total,
                        ]),

        'por_comuna' => $this->countByLabel('communes', 'commune_id', 'name', 'Sin comuna', 10),

        'por_anio_entrada' => DemandManagementWaitlist::query()
                        ->whereNotNull('entry_date')
                        ->selectRaw('YEAR(entry_date) as year, COUNT(*) as total')
                        ->groupBy('year')
                        ->orderBy('year')
                        ->pluck('total', 'year'),
    ];

    // Waiting time of the open cases, in days since F_ENTRADA.
    $waits = DemandManagementWaitlist::query()->open()->whereNotNull('entry_date')
        ->selectRaw('DATEDIFF(CURDATE(), entry_date) as days')
        ->orderBy('days')
        ->pluck('days')
        ->map(fn ($d) => (int) $d);

    $this->waitlistStats = [
        'abiertos'          => DemandManagementWaitlist::query()->open()->count(),
        'cerrados'          => DemandManagementWaitlist::query()->discharged()->count(),
        'ges'               => DemandManagementWaitlist::where('ges', true)->count(),
        'diagnostico_ges'   => DemandManagementWaitlist::query()->whereNotNull('cie10_id')
                                ->whereHas('cie10', fn ($q) => GesHealthProblem::whereCie10InProblems($q, DB::table('ges_health_problems')->pluck('id')->all()))
                                ->count(),
        'espera_promedio'   => $waits->isNotEmpty() ? (int) round($waits->avg()) : null,
        'espera_mediana'    => $waits->isNotEmpty() ? $waits->median() : null,
        'mas_de_un_anio'    => $waits->filter(fn ($d) => $d >= 365)->count(),
    ];
}

    /**
     * Rows per value of a lookup table (label column), most frequent first.
     */
    private function countByLabel(string $table, string $foreignKey, string $labelColumn, string $empty, ?int $limit = null)
    {
        return DemandManagementWaitlist::query()
            ->leftJoin("{$table} as lookup", 'lookup.id', '=', "demand_management_waitlists.{$foreignKey}")
            ->selectRaw("COALESCE(lookup.{$labelColumn}, ?) as label, COUNT(*) as total", [$empty])
            ->groupBy('label')
            ->orderByDesc('total')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->pluck('total', 'label');
    }

    /**
     * Rows per range of a numeric SQL expression, in range order.
     *
     * @param array<int, array{0: int, 1: ?int, 2: string}> $buckets [from, to (inclusive, null = open), label]
     */
    private function countByBuckets($query, string $expression, array $buckets, string $empty): array
    {
        $cases = collect($buckets)->map(fn ($b) => $b[1] === null
            ? "WHEN {$expression} >= {$b[0]} THEN '{$b[2]}'"
            : "WHEN {$expression} BETWEEN {$b[0]} AND {$b[1]} THEN '{$b[2]}'")->implode(' ');

        $counts = $query->selectRaw("CASE {$cases} ELSE ? END as label, COUNT(*) as total", [$empty])
            ->groupBy('label')
            ->pluck('total', 'label');

        // Fixed order (ranges first, empty last), without empty ranges.
        return collect(array_column($buckets, 2))->push($empty)
            ->mapWithKeys(fn ($label) => [$label => (int) ($counts[$label] ?? 0)])
            ->filter()
            ->all();
    }
}
