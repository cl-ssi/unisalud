<?php

namespace App\Livewire\Condition;

use app\Models\User;
use Livewire\Attributes\On;
// use Filament\Support\Enums\FontWeight;
use Livewire\Component;
use Filament\Forms;
use Filament\Infolists;
use Carbon\Carbon;

class InfoUser extends Component implements Forms\Contracts\HasForms, Infolists\Contracts\HasInfolists
{

    use Infolists\Concerns\InteractsWithInfolists;
    use Forms\Concerns\InteractsWithForms;

    public ?User $user;

    public ?string $user_id;

    #[On('updateUserId')]
    public function mount(?string $user_id = null): void
    {
        $this->user_id = $this->user_id??$user_id;
        $this->user = User::find($this->user_id);
    }

    public function userInfolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->record($this->user)
            ->schema([
                Infolists\Components\Fieldset::make('User')
                    ->label(fn (User $record): string => $record->text . ', ' . Carbon::parse($record->birthday)->age . ' Años')
                    ->schema([
                        Infolists\Components\TextEntry::make('RUT')
                            ->label('RUT')
                            ->state(fn (User $record): string => $record->identifiers->first()->value . '-' . $record->identifiers->first()->dv),
                        Infolists\Components\TextEntry::make('Direccion')
                            ->state(fn (User $record): string => $record->address->text . ' ' . $record->address->line . ', ' . $record->address->commune->name),
                        Infolists\Components\TextEntry::make('birthday')
                            ->label('Fecha Nacimiento')
                            ->date(),
                        Infolists\Components\TextEntry::make('sex')
                            ->label('Sexo'),
                        Infolists\Components\TextEntry::make('gender')
                            ->label('Genero'),
                    ])->columns(5)
            ]);
    }



    public function render()
    {
        return view('livewire.condition.info-user');
    }
}
