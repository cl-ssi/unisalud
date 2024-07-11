<?php

namespace App\Filament\Resources\SexResource\Pages;

use App\Filament\Resources\SexResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSex extends EditRecord
{
    protected static string $resource = SexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
