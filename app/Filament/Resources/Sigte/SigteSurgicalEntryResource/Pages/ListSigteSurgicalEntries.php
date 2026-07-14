<?php

namespace App\Filament\Resources\Sigte\SigteSurgicalEntryResource\Pages;

use App\Filament\Resources\Sigte\SigteSurgicalEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSigteSurgicalEntries extends ListRecords
{
    protected static string $resource = SigteSurgicalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ingresar Paciente'),
        ];
    }
}
