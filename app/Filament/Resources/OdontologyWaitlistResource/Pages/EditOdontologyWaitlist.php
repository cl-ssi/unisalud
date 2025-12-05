<?php

namespace App\Filament\Resources\OdontologyWaitlistResource\Pages;

use App\Filament\Resources\OdontologyWaitlistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOdontologyWaitlist extends EditRecord
{
    protected static string $resource = OdontologyWaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
