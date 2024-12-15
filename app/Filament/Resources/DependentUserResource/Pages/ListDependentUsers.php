<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDependentUsers extends ListRecords
{
    protected static string $resource = DependentUserResource::class;

    protected function getHeaderActions(): array
    {
        
            if(auth()->user()->can('be god')){
                return [Actions\CreateAction::make()];
            } else {
                return [];
            }
    }
}
