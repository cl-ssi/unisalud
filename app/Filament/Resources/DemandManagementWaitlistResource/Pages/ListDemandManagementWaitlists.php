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

    protected function getHeaderWidgets(): array
    {
        return [
            DemandManagementWaitlistResource\Widgets\DemandManagementImportProgress::class,
        ];
    }

    public function getSubNavigation(): array
    {
        return static::getResource()::getSectionSubNavigation();
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'abiertos';
    }

    public function getTabs(): array
    {
        return [
            'todos'    => Tab::make('Todos'),
            // Egresado = F_SALIDA and C_SALIDA, except C_SALIDA 3 / 20
            // (DemandManagementWaitlist::scopeDischarged / scopeOpen).
            'abiertos' => Tab::make('Abiertos')
                ->modifyQueryUsing(fn (Builder $query) => $query->open()),
            'cerrados' => Tab::make('Cerrados')
                ->modifyQueryUsing(fn (Builder $query) => $query->discharged()),
        ];
    }
}
