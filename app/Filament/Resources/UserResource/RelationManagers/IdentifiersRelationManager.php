<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IdentifiersRelationManager extends RelationManager
{
    protected static string $relationship = 'identifiers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('use'),
                Forms\Components\Select::make('cod_con_identifier_type_id')
                    ->relationship('codConIdentifierType', 'text')
                    ->default(null),
                Forms\Components\TextInput::make('system')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('value')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('dv')
                    ->maxLength(255)
                    ->default(null),
                // Forms\Components\TextInput::make('period_id')
                //     ->numeric()
                //     ->default(null),
                // Forms\Components\TextInput::make('practitioner_id')
                //     ->numeric()
                //     ->default(null),
                // Forms\Components\TextInput::make('organization_id')
                //     ->numeric()
                //     ->default(null),
                // Forms\Components\TextInput::make('appointment_id')
                //     ->numeric()
                //     ->default(null),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('use')
            ->columns([
                Tables\Columns\TextColumn::make('use'),
                Tables\Columns\TextColumn::make('codConIdentifierType.text')
                    ->sortable(),
                Tables\Columns\TextColumn::make('system')
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dv')
                    ->searchable(),
                Tables\Columns\TextColumn::make('period_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('practitioner_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('organization_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('appointment_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
