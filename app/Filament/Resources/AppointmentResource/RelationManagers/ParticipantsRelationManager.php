<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

use App\Enums\ParticipantRequired;
use App\Enums\ParticipantStatus;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('participant_id')
                    ->numeric(),
                Forms\Components\TextInput::make('appointment_id')
                    ->numeric(),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('period_id')
                    ->numeric(),
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->required()
                    ->relationship('user','text')
                    ->options(User::all()->pluck('text'))
                    ->searchable(),
                Forms\Components\TextInput::make('location_id')
                    ->numeric(),
                Forms\Components\Select::make('required')
                    ->label('Requerido')
                    ->options(ParticipantRequired::class)
                    ->reactive(),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options(ParticipantStatus::class)
                    ->reactive(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
