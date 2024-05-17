<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IdentifierResource\Pages;
use App\Filament\Resources\IdentifierResource\RelationManagers;
use App\Models\Identifier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IdentifierResource extends Resource
{
    protected static ?string $model = Identifier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('identifiers_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->default(null),
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
                Forms\Components\TextInput::make('period_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('practitioner_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('organization_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('appointment_id')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('identifiers_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.id')
                    ->numeric()
                    ->sortable(),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('practitioner_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('organization_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment_id')
                    ->numeric()
                    ->sortable(),
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
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIdentifiers::route('/'),
            'create' => Pages\CreateIdentifier::route('/create'),
            'edit' => Pages\EditIdentifier::route('/{record}/edit'),
        ];
    }
}
