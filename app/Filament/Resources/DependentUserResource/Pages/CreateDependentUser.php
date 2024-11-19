<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDependentUser extends CreateRecord
{
    protected static string $resource = DependentUserResource::class;
}
