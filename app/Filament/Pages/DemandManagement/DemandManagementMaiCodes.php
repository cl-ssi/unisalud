<?php

namespace App\Filament\Pages\DemandManagement;

use App\Models\DemandManagementSpeciality;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only view of the Gestión Demanda Código MAI catalog: PRESTA_MIN
 * values accepted for TIPO_PREST 4 (Prestaciones_c-fechavigencia.xlsx,
 * hoja "prest total").
 */
class DemandManagementMaiCodes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Lista De Espera';

    protected static ?string $navigationLabel = 'Códigos Especialidad';

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $title = 'Códigos Especialidad Gestión Demanda (TIPO_PREST 4)';

    protected static ?string $slug = 'gestion-demanda-codigos-especialidad';

    protected static string $view = 'filament.pages.demand-management.demand-management-mai-codes';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('DemandManagementWaitlist: View');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DemandManagementSpeciality::query()
                    ->whereNotNull('code')
                    ->withCount('waitlists')
            )
            ->defaultSort('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código MAI')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Glosa')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('text')
                    ->label('Especialidad')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waitlists_count')
                    ->label('Pacientes en lista')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('text')
                    ->label('Especialidad')
                    ->options(fn () => DemandManagementSpeciality::query()
                        ->whereNotNull('code')
                        ->distinct()
                        ->orderBy('text')
                        ->pluck('text', 'text'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('in_use')
                    ->label('Con pacientes')
                    ->placeholder('Todos')
                    ->trueLabel('Con pacientes')
                    ->falseLabel('Sin pacientes')
                    ->queries(
                        true: fn (Builder $query) => $query->has('waitlists'),
                        false: fn (Builder $query) => $query->doesntHave('waitlists'),
                    ),
            ])
            ->paginated([25, 50, 100]);
    }
}
