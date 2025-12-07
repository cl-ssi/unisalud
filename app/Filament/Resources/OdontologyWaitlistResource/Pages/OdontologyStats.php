<?php

namespace App\Filament\Resources\OdontologyWaitlistResource\Pages;

use App\Filament\Resources\OdontologyWaitlistResource;
use App\Models\OdontologyWaitlist;
use Filament\Resources\Pages\Page;

class OdontologyStats extends Page
{
    protected static string $resource = OdontologyWaitlistResource::class;

    protected static string $view = 'filament.resources.odontology-waitlist-resource.pages.odontology-stats';

    /** 👇 ESTA PROPIEDAD ES OBLIGATORIA */
    public array $stats = [];

    public function mount(): void
{
    $this->stats = [
        'total'          => OdontologyWaitlist::count(),
        'primer_llamado' => OdontologyWaitlist::where('status', 'primer llamado')->count(),
        'segundo_llamado'=> OdontologyWaitlist::where('status', 'segundo llamado')->count(),
        'citado'         => OdontologyWaitlist::where('status', 'citado')->count(),
        'atendidos'      => OdontologyWaitlist::whereIn('status', [
            'atendido praps', 'atendido hetg', 'atendido hah'
        ])->count(),
        'fallecidos'     => OdontologyWaitlist::where('status', 'fallecido')->count(),
    ];

    // Estadísticas adicionales para gráficos
    $this->chartStats = [
        'por_estado' => OdontologyWaitlist::selectRaw('status, COUNT(*) as total')
                        ->groupBy('status')
                        ->pluck('total', 'status'),

        'por_establecimiento' => OdontologyWaitlist::selectRaw('establishment_id, COUNT(*) as total')
                        ->groupBy('establishment_id')
                        ->with('establishment:id,alias')
                        ->get()
                        ->mapWithKeys(fn($row) => [
                            optional($row->establishment)->alias ?? 'Sin Establecimiento' => $row->total
                        ]),

        'por_especialidad' => OdontologyWaitlist::selectRaw('specialty_id, COUNT(*) as total')
                        ->groupBy('specialty_id')
                        ->with('waitlistSpecialty:id,text')
                        ->get()
                        ->mapWithKeys(fn($row) => [
                            optional($row->waitlistSpecialty)->text ?? 'Sin Especialidad' => $row->total
                        ]),
    ];
}
}
