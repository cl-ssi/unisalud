<?php

declare(strict_types=1);

namespace App\Filament\Resources\VentoxUserResource\Pages;

use App\Filament\Resources\VentoxUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVentoxUser extends EditRecord
{
    protected static string $resource = VentoxUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
