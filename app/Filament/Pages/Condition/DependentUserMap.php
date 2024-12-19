<?php

namespace App\Filament\Pages\Condition;

use Filament\Pages\Page;
use Filament\Forms\Components\Select;

use App\Models\User;
use App\Models\DependentUser;
use App\Models\Condition;

// use Illuminate\Database\Eloquent\Builder;

class DependentUserMap extends Page
{
    protected static ?string $navigationLabel = 'Mapa Pacientes con Condición';
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static string $view = 'filament.pages.condition.dependent-user-map';
    protected static ?string $navigationGroup = 'Usuarios';
    // protected static ?string $slug = 'condition-map';

    protected static ?string $title = 'Mapa de Pacientes con Condición';

    // public $selectedCondition = null;
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

    protected function getFormSchema(): array
    {
        return [
            Select::make('condition_id')
                ->label('Select Condition')
                ->options($this->conditionTypes)
                ->required()
                ->reactive() // Hacer que el select sea reactivo
                ->afterStateUpdated(fn ($state) => $this->condition_id =$state), // Llamar a un método cuando se actualice
        ];
    }
    
}
