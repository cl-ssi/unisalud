<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Imports\ConditionImporter;
use App\Filament\Resources\DependentUserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Filament\Actions;

use App\Models\Condition;
use App\Models\DependentUser;

use Illuminate\Database\Eloquent\Builder;
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
                        ->modalHeading('Importar Usuarios Dependientes')
                        ->modalSubmitActionLabel('Importar'),
                    Actions\Action::make('map')
                        ->url(route('filament.admin.resources.dependent-users.map'))
                        ->label('Ver Mapa'),
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
                // ->badge($tier->countDependents())
                ->modifyQueryUsing(function ($query) use ($tier) {
                    return $query->whereHas('conditions', function($query) use ($tier) {
                        $query->where('condition_id', $tier->id);
                    });
                });
        }
        return $tabs;
    }
}
