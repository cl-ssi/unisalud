<?php

namespace App\Filament\Resources\Sigte\SigteSurgicalExportResource\Pages;

use App\Filament\Resources\Sigte\SigteSurgicalExportResource;
use Filament\Resources\Pages\ListRecords;

class ListSigteSurgicalExports extends ListRecords
{
    protected static string $resource = SigteSurgicalExportResource::class;

    protected static ?string $title = 'Exportaciones SIGTE';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
