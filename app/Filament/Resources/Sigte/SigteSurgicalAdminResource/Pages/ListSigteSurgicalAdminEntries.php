<?php

namespace App\Filament\Resources\Sigte\SigteSurgicalAdminResource\Pages;

use App\Enums\SurgicalComplexity;
use App\Exports\SigteSurgicalExport;
use App\Filament\Resources\Sigte\SigteSurgicalAdminResource;
use App\Filament\Widgets\SigteSurgicalStatsWidget;
use App\Models\SigteSurgicalExportBatch;
use App\Models\SigteSurgicalWaitlist;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListSigteSurgicalAdminEntries extends ListRecords
{
    protected static string $resource = SigteSurgicalAdminResource::class;

    protected static ?string $title = 'Todos los Ingresos';

    protected function getHeaderWidgets(): array
    {
        return [
            SigteSurgicalStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar')
                ->label('Exportar SIGTE')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    DatePicker::make('desde')
                        ->label('Desde (F. Entrada)')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    DatePicker::make('hasta')
                        ->label('Hasta (F. Entrada)')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    Select::make('requesting_professional_id')
                        ->label('Cirujano')
                        ->placeholder('Todos')
                        ->options(fn () => User::whereIn(
                            'id',
                            SigteSurgicalWaitlist::distinct()->pluck('requesting_professional_id')
                        )->get()->mapWithKeys(fn ($u) => [
                            $u->id => $u->text ?: trim("{$u->given} {$u->fathers_family}"),
                        ]))
                        ->searchable(),
                    Select::make('status')
                        ->label('Estado')
                        ->placeholder('Todos')
                        ->options([
                            'completo'   => 'Completo',
                            'incompleto' => 'Incompleto',
                        ]),
                    Select::make('complexity')
                        ->label('Complejidad')
                        ->placeholder('Todas')
                        ->options(
                            collect(SurgicalComplexity::cases())
                                ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
                        ),
                ])
                ->action(function (array $data) {
                    $filters = array_filter([
                        'desde'                       => $data['desde'] ?? null,
                        'hasta'                        => $data['hasta'] ?? null,
                        'requesting_professional_id'  => $data['requesting_professional_id'] ?? null,
                        'status'                       => $data['status'] ?? null,
                        'complexity'                   => $data['complexity'] ?? null,
                    ]);

                    $matchingIds = SigteSurgicalExport::query($filters)->pluck('id');

                    SigteSurgicalWaitlist::whereIn('id', $matchingIds)
                        ->update(['exported_at' => now(), 'exported_by' => auth()->id()]);

                    $batch = SigteSurgicalExportBatch::create([
                        'exported_by'                 => auth()->id(),
                        'desde'                        => $filters['desde'] ?? null,
                        'hasta'                        => $filters['hasta'] ?? null,
                        'requesting_professional_id'  => $filters['requesting_professional_id'] ?? null,
                        'status'                       => $filters['status'] ?? null,
                        'complexity'                   => $filters['complexity'] ?? null,
                        'patients_count'               => $matchingIds->count(),
                    ]);
                    $batch->waitlistEntries()->attach(
                        $matchingIds->mapWithKeys(fn ($id) => [$id => ['created_at' => now()]])
                    );

                    $suffix = (($filters['desde'] ?? null) || ($filters['hasta'] ?? null))
                        ? '_' . ($filters['desde'] ?? 'inicio') . '_' . ($filters['hasta'] ?? 'hoy')
                        : '_' . now()->format('Y-m-d');

                    return Excel::download(
                        new SigteSurgicalExport($matchingIds->all()),
                        'SIGTE_LEQx' . $suffix . '.xlsx'
                    );
                }),
        ];
    }
}
