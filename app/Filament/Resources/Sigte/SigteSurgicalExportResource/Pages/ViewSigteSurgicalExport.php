<?php

namespace App\Filament\Resources\Sigte\SigteSurgicalExportResource\Pages;

use App\Filament\Resources\Sigte\SigteSurgicalExportResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSigteSurgicalExport extends ViewRecord
{
    protected static string $resource = SigteSurgicalExportResource::class;

    protected static ?string $title = 'Detalle de Exportación';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
