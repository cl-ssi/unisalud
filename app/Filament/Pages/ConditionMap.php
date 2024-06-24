<?php

namespace App\Filament\Pages;

use App\Models\Coding;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use App\Models\Condition;
use App\Models\User;

class ConditionMap extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static string $view = 'filament.pages.condition-map';
    protected static ?string $navigationGroup = 'Usuarios';

    public $selectedCondition = null;
    public $users = [];

    public function mount()
    {
        $this->users = $this->getUsersForCondition();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('selectedCondition')
                ->label('Select Condition')
                ->options(Coding::pluck('display', 'id')->toArray())
                ->reactive()
                ->afterStateUpdated(fn () => $this->users = $this->getUsersForCondition()),
        ];
    }

    public function getUsersForCondition($coding_id = null)
    {
        if (!$this->selectedCondition) {
            return [];
        }
    
        return User::whereHas('conditions', function ($query) {
                $query->where('conditions.id', $this->selectedCondition);
            })
            ->with(['address.location']) // Cargamos las relaciones
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->text,
                    'sex' => $user->sex,
                    'latitude' => $user->address->location->latitude ?? null,
                    'longitude' => $user->address->location->longitude ?? null,
                ];
            })
            ->filter(function ($user) {
                return $user['latitude'] !== null && $user['longitude'] !== null;
            })
            ->values()
            ->toArray();
    }
}