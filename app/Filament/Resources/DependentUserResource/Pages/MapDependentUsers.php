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
    public $condition_id = null;
    public $user_id = null;


    public function mount(): void
    {
        $this->condition_id = $this->condition_id??request('condition_id');
        $this->user_id = $this->user_id??request('user_id');
        $this->conditionTypes = Condition::pluck('name', 'id')->toArray();
        $this->form->fill([
            'condition_id' => $this->condition_id,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('condition_id')
                    ->label('Select Condition')
                    ->options($this->conditionTypes)
                    ->required()
                    ->reactive() // Hacer que el select sea reactivo
                    ->afterStateUpdated(fn ($state) => $this->condition_id =$state), // Llamar a un método cuando se actualice
            ]);
    }

    protected function getHeaderActions(): array
    {
        if($this->condition_id && $this->user_id){
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
}
