<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Select;

use App\Models\User;
use App\Models\DependentUser;
use App\Models\DependentConditions;
use App\Models\Condition;

// use Illuminate\Database\Eloquent\Builder;

class ConditionMap extends Page
{
    protected static ?string $navigationLabel = 'Mapa Pacientes con Condición';
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static string $view = 'filament.pages.condition-map';
    protected static ?string $navigationGroup = 'Usuarios';

    protected static ?string $title = 'Mapa de Pacientes con Condición';

    // public $selectedCondition = null;
    public $users = [];
    public $conditionTypes = [];
    public $condition_id = null;


    public function mount()
    {
        $this->conditionTypes = Condition::pluck('name', 'id')->toArray();
        $this->form->fill([
            'condition_id' => null,
        ]);

        $this->users = $this->getUsersForCondition();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('condition_id')
                ->label('Select Condition')
                ->options($this->conditionTypes)
                ->required()
                ->reactive() // Hacer que el select sea reactivo
                ->afterStateUpdated(fn ($state) => $this->updatedConditionId($state)), // Llamar a un método cuando se actualice
        ];
    }

    public function updatedConditionId($conditionId): void
    {
        $this->condition_id = $conditionId;
        $this->users = $this->getUsersForCondition();
    }

    public function getUsersForCondition(): array
    {
        if (!$this->condition_id) {
            return [];
        }


        return User::whereHas('dependentUser', function ($query) {
                $query->whereHas('dependentConditions', function ($query) {
                    $query->where('condition_id', '=', $this->condition_id);
                });
            })
            ->with(['address.location', 'dependentUser']) // Cargamos las relaciones
            ->get()
            ->map(function ($user) {
                return [
                    'id'            => $user->id,
                    'name'          => $user->text,
                    'sex'           => $user->sex,
                    'latitude'      => $user->address->location->latitude ?? null,
                    'longitude'     => $user->address->location->longitude ?? null,
                    'diagnostico'   => $user->dependentUser->diagnosis,
                ];
            })
            ->filter(function ($user) {
                return $user['latitude'] !== null && $user['longitude'] !== null;
            })
            ->values()
            ->toArray();
    }
}
