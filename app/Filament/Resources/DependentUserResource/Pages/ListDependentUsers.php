<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Imports\ConditionImporter;
use App\Filament\Resources\DependentUserResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use Filament\Actions;
use App\Models\Condition;
use Illuminate\Database\Eloquent\Model;

class ListDependentUsers extends ListRecords
{
    protected static string $resource = DependentUserResource::class;

    protected function getHeaderActions(): array
    {
        
            if(auth()->user()->can('be god')){
                return [
                    Actions\CreateAction::make(),
                    Actions\ImportAction::make()
                        ->importer(ConditionImporter::class)
                        ->label('Importar Condición de Usuarios')
                        ->modalHeading('Importar Condición de Usuarios')
                        // ->modalDescription('Subir archivo CSV')
                        ->modalSubmitActionLabel('Importar')
                        // ->options([])
                ];
            } else {
                return [];
            }
    }

    public function getTabs(): array
    {
        $tiers = Condition::get();
        $tabs = ['Todos' => Tab::make('Todos')->badge($this->getModel()::count())];

        foreach ($tiers as $tier) {
            $name = ucwords($tier->name);
            $slug = str($name)->slug()->toString();
 
            $tabs[$slug] = Tab::make($name)
                // ->badge($tier->customers_count)
                ->modifyQueryUsing(function ($query) use ($tier) {
                    return $query->whereHas('dependentConditions', function($query) use ($tier) {
                        $query->where('condition_id', $tier->id);
                    });
                });
        }
        return $tabs;
    }
}
