<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

use App\Livewire\DependentUser\CreateDependentUser as CreateDependentUserLivewire;

class CreateDependentUser extends CreateRecord
{
    protected static string $resource = DependentUserResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Livewire::make(CreateDependentUserLivewire::class)
                    ->label('Crear Paciente Dependiente')
                    ->columnSpanFull(),
            ]);
    }
}
