<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

use YOS\FilamentExcel\Actions\Import;
use App\Imports\DependentUserImport;
use pxlrbt\FilamentExcel;

use App\Filament\Pages\Concerns\HasHeadingIcon;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

class ListDependentUsers extends ListRecords
{
    use HasHeadingIcon;

    public function getHeading(): string|Htmlable
    {
        /* 
        return new HtmlString($this->getHeadingWithIcon(
            heading: 'Dependientes severos',
            icon: 'icon-dependent-temp',
            iconPosition: IconPosition::Before,
            iconSize: IconSize::Large
        )); 
        */

        return new HtmlString(Blade::render('<div class="flex items-center">
                <x-icon-dependent-temp 
                style="--c-600: var(--black-600);margin-inline-end: .5rem; width: 5rem; height: 5rem;"
                class="inline text-custom-600"
                /> Dependientes Severos 
            </div>'));
    }

    protected static string $resource = DependentUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo')
                ->icon('heroicon-o-user-plus'),
            Actions\Action::make('map')
                ->url(fn(\Livewire\Component $livewire) => route(
                    'filament.admin.resources.dependent-users.map',
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
                        ->withFilename(fn($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                ]),
            Import::make()
                ->import(DependentUserImport::class)
                ->type(\Maatwebsite\Excel\Excel::XLSX)
                ->label('Importar')
                ->visible(auth()->user()->can('be god'))
                ->hint('Subir archivo de tipo xlsx')
                ->icon('heroicon-o-arrow-down-tray'),

        ];
    }

    public function getTabs(): array
    {
        return [];
    }
}
