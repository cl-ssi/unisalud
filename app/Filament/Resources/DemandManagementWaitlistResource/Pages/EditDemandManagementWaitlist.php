<?php

namespace App\Filament\Resources\DemandManagementWaitlistResource\Pages;

use App\Filament\Resources\DemandManagementWaitlistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDemandManagementWaitlist extends EditRecord
{
    protected static string $resource = DemandManagementWaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
