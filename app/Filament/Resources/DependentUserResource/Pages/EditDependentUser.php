<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDependentUser extends EditRecord
{
    protected static string $resource = DependentUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
