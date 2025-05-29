<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Imports\ConditionImporter;
use App\Filament\Resources\DependentUserResource;
use Filament\Resources\Pages\ListRecords;

use Filament\Actions;

use pxlrbt\FilamentExcel;
use YOS\FilamentExcel\Actions\Import;



class ListDependentUsers extends ListRecords
{
    protected static string $resource = DependentUserResource::class;

    protected function getHeaderActions(): array
    {
                return [
                    // Actions\CreateAction::make()
                    //     ->label('Nuevo')
                    //     ->icon('heroicon-o-user-plus'),
                    Actions\Action::make('map')
                        ->url(fn () => route('filament.admin.resources.dependent-users.map', request()->query()))
                        ->icon('heroicon-o-map')
                        ->label('Mapa'),
                    FilamentExcel\Actions\Pages\ExportAction::make()
                        ->label('Exportar')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->exports([
                            FilamentExcel\Exports\ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                                ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                        ]),
                    Import::make()
                        ->import(\App\Imports\DependentUserImport::class)
                        ->type(\Maatwebsite\Excel\Excel::XLSX)
                        ->label('Importar XLSX')
                        ->hint('Subir archivo de tipo xlsx')
                        ->icon('heroicon-o-arrow-down-tray'),
                        
                ];
            }
    }

    public function getTabs(): array
    {
        return [];
    }
}
