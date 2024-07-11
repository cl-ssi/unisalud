<?php

namespace App\Filament\Resources\SexResource\Pages;

use App\Filament\Resources\SexResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSexes extends ListRecords
{
    protected static string $resource = SexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
