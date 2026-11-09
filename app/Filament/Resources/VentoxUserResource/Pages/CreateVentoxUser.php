<?php

declare(strict_types=1);

namespace App\Filament\Resources\VentoxUserResource\Pages;

use App\Filament\Resources\VentoxUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVentoxUser extends CreateRecord
{
    protected static string $resource = VentoxUserResource::class;
}
