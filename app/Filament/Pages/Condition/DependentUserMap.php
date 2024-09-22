<?php

namespace App\Filament\Pages\Condition;

use Filament\Pages\Page;
use Filament\Forms\Components\Select;

use App\Models\User;
use App\Models\DependentUser;
use App\Models\DependentConditions;
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
            ->with(['address', 'address.location', 'address.commune', 'dependentUser']) // Cargamos las relaciones
            ->get()
            ->map(function ($user) {
                return [
                    'id'            => $user->id,
                    'name'          => $user->text,
                    'sex'           => $user->sex,
                    'latitude'      => $user->address->location->latitude ?? null,
                    'longitude'     => $user->address->location->longitude ?? null,
                    'diagnostico'   => $user->dependentUser->diagnosis,
                    'calle'         => $user->address->text ?? null,
                    'numero'        => $user->address->line ?? null,
                    'departamento'  => $user->address->apartament ?? null,
                    'comuna'        => $user->address->commune->name ?? null,
                ];
            })
            ->filter(function ($user) {
                if($this->user_id != null){
                    return $user['latitude'] !== null && $user['longitude'] !== null && $user['id'] == $this->user_id;
                } else {
                    return $user['latitude'] !== null && $user['longitude'] !== null;
                }
            })
            ->values()
            ->toArray();
    }
}
