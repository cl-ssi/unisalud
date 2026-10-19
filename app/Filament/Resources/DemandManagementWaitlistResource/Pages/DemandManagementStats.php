<?php

namespace App\Filament\Resources\DemandManagementWaitlistResource\Pages;

use App\Filament\Resources\DemandManagementWaitlistResource;
use App\Models\DemandManagementWaitlist;
use Filament\Resources\Pages\Page;

class DemandManagementStats extends Page
{
    protected static string $resource = DemandManagementWaitlistResource::class;

    protected static string $view = 'filament.resources.demand-management-waitlist-resource.pages.demand-management-stats';

    /** 👇 ESTA PROPIEDAD ES OBLIGATORIA */
    public array $stats = [];

    public array $chartStats = [];

    public array $estadoBreakdown = [];

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
        'por_estado' => DemandManagementWaitlist::selectRaw('status, COUNT(*) as total')
                        ->groupBy('status')
                        ->pluck('total', 'status'),

        'por_establecimiento' => DemandManagementWaitlist::selectRaw('establishment_id, COUNT(*) as total')
                        ->groupBy('establishment_id')
                        ->with('establishment:id,alias')
                        ->get()
                        ->mapWithKeys(fn($row) => [
                            optional($row->establishment)->alias ?? 'Sin Establecimiento' => $row->total
                        ]),

        'por_especialidad' => DemandManagementWaitlist::selectRaw('specialty_id, COUNT(*) as total')
                        ->groupBy('specialty_id')
                        ->with('waitlistSpecialty:id,text')
                        ->get()
                        ->mapWithKeys(fn($row) => [
                            optional($row->waitlistSpecialty)->text ?? 'Sin Especialidad' => $row->total
                        ]),
    ];
}
}
