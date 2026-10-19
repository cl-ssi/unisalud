<?php

namespace App\Filament\Resources\DemandManagementWaitlistResource\Pages;

use App\Filament\Resources\DemandManagementWaitlistResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDemandManagementWaitlists extends ListRecords
{
    protected static string $resource = DemandManagementWaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos'    => Tab::make('Todos'),
            'abiertos' => Tab::make('Abiertos')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNull('exit_date')
                    ->where(fn (Builder $q) => $q->whereNull('exit_code')->orWhere('exit_code', ''))
                    ->whereNull('exit_minsal_specialty_id')),
            'cerrados' => Tab::make('Cerrados')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where(fn (Builder $q) => $q
                        ->whereNotNull('exit_date')
                        ->orWhere(fn (Builder $q2) => $q2->whereNotNull('exit_code')->where('exit_code', '!=', ''))
                        ->orWhereNotNull('exit_minsal_specialty_id'))),
        ];
    }
}
