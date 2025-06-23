<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Imports\ConditionImporter;
use App\Filament\Resources\DependentUserResource;
use Filament\Resources\Pages\ListRecords;

use Filament\Actions;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel;
use Symfony\Component\CssSelector\Node\FunctionNode;
use YOS\FilamentExcel\Actions\Import;

use App\Filament\Pages\Concerns\HasHeadingIcon;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListDependentUsers extends ListRecords
{
    use HasHeadingIcon;

    public function getHeading(): string|Htmlable
    {
        return new HtmlString($this->getHeadingWithIcon(
            heading: 'Pacientes dependientes severos',
            icon: 'heroicon-o-rectangle-stack',
            iconColor: 'black',
            iconPosition: IconPosition::Before,
            iconSize: IconSize::Medium
        ));
    }

    protected static string $resource = DependentUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->label('Nuevo')
            //     ->icon('heroicon-o-user-plus'),
            Actions\Action::make('map')
                ->url(fn (\Livewire\Component $livewire) => route('filament.admin.resources.dependent-users.map', 
                    [
                        'conditions_id' => $livewire->getTable()->getFilters()['conditions']->getState('name')['values'] ?? null,
                        'search' => $livewire->getTable()->getFilters()['user']->getForm()->getState()['name'] ?? null,
                        'organizations_id' => $livewire->getTable()->getFilters()['user.mobileContactPoint.organization']->getState()['values'] ?? null,
                    ]
                ))
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
                ->label('Importar')
                ->hint('Subir archivo de tipo xlsx')
                ->icon('heroicon-o-arrow-down-tray'),
                
        ];
            
    }

    public function getTabs(): array
    {
        return [];
    }
}
