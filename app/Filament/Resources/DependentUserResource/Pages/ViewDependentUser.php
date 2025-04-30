<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Schema\View;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewDependentUser extends ViewRecord
{
    protected static string $resource = DependentUserResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('RUT')
                    ->label('RUT')
                    ->getStateUsing(fn (Model $record): string => $record->user->identifiers->first()->value . '-' . $record->user->identifiers->first()->dv),
                Infolists\Components\TextEntry::make('Direccion')
                    ->getStateUsing(fn (Model $record): string => $record->user->address->text . ' ' . $record->user->address->line . ', ' . $record->user->address->commune->name),
                Infolists\Components\TextEntry::make('birthday')
                    ->label('Fecha Nacimiento')
                    ->date(),
                Infolists\Components\TextEntry::make('sex')
                    ->label('Sexo'),
                Infolists\Components\TextEntry::make('gender')
                    ->label('Genero'),
            ]);
    }

}
