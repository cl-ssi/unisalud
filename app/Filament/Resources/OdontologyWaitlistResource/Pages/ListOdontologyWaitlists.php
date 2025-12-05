<?php

namespace App\Filament\Resources\OdontologyWaitlistResource\Pages;

use App\Filament\Resources\OdontologyWaitlistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOdontologyWaitlists extends ListRecords
{
    protected static string $resource = OdontologyWaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
