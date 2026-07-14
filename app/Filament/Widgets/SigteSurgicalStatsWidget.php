<?php

namespace App\Filament\Widgets;

use App\Models\SigteSurgicalWaitlist;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SigteSurgicalStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total       = SigteSurgicalWaitlist::count();
        $hoy         = SigteSurgicalWaitlist::whereDate('created_at', today())->count();
        $completo    = SigteSurgicalWaitlist::where('status', 'completo')->count();
        $incompleto  = SigteSurgicalWaitlist::where('status', 'incompleto')->count();
        $ultimaExportacion = SigteSurgicalWaitlist::max('exported_at');

        return [
            Stat::make('Total Ingresos', $total)
                ->icon('heroicon-o-clipboard-document-list'),
            Stat::make('Hoy', $hoy)
                ->icon('heroicon-o-calendar-days')
                ->color('primary'),
            Stat::make('Completos', $completo)
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Incompletos', $incompleto)
                ->icon('heroicon-o-clock')
                ->color('gray'),
            Stat::make('Última Exportación', $ultimaExportacion
                ? \Illuminate\Support\Carbon::parse($ultimaExportacion)->format('d-m-Y H:i')
                : 'Nunca')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary'),
        ];
    }
}
