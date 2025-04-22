<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use App\Models\Condition;
use App\Models\DependentUser;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

class MapDependentUsers extends Page
{
    protected static string $resource = DependentUserResource::class;

    protected static string $view = 'filament.resources.dependent-user-resource.pages.map-dependent-users';

    protected static ?string $title = 'Mapa de Pacientes con Condición';

    public $users = [];
    public $conditionTypes = [];
    public $conditions_id = null;
    public $user_id = null;


    public function mount(): void
    {
        $this->conditions_id = $this->conditions_id??request('conditions_id');
        $this->user_id = $this->user_id??request('user_id');
        $this->conditionTypes = Condition::pluck('name', 'id')->toArray();
        $this->form->fill([
            'conditions_id' => $this->conditions_id,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('conditions_id')
                    ->label('Condición')
                    ->options($this->conditionTypes)
                    ->required()
                    ->placeholder('Seleccione una condición')
                    ->multiple()
                    ->reactive() // Hacer que el select sea reactivo
                    ->afterStateUpdated(fn ($state) => $this->conditions_id =$state), // Llamar a un método cuando se actualice
            ]);
    }

    protected function getHeaderActions(): array
    {
        if($this->conditions_id && $this->user_id){
            return [
                Actions\Action::make('Volver')
                    ->url(DependentUserResource::getUrl())
                    ->button()
                    ->color('info'),
            ];
        }
        else {
            return [];
        }
    }

    protected function getFooterWidgets(): array
    {
        return [
            'map' => \App\Filament\Resources\DependentUserResource\Widgets\MapWidget::class,
        ];
    }

    public function updated($property)
    {
        $this->dispatch('changeFilters', $this->conditions_id, $this->user_id);
    }
}
