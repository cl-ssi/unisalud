<?php

namespace App\Filament\Resources\WaitlistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

class WaitlistMessageRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Mensajes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('priority')
                    ->label('Prioridad')
                    ->options([
                        'alta'      => 'Alta',
                        'baja'      => 'Baja',
                        'normal'    => 'Normal',
                    ])
                    ->default('normal') // Valor por defecto para el campo de prioridad
                    ->columnSpan('full')
                    ->required(),

                Forms\Components\TextInput::make('subject')
                    ->label('Asunto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan('full'),

                Forms\Components\Textarea::make('message')
                    ->label('Mensaje')
                    ->required()
                    ->columnSpan('full'),

                Forms\Components\Select::make('to_user_id')
                    ->label('Para (Usuario)')
                    ->options(User::whereNotNull('text')->pluck('text', 'id')) // Filtrar usuarios con nombre no nulo
                    ->searchable() // Permite la búsqueda por nombre
                    ->columnSpan('full'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state); // Formatear el estado para que se muestre con la primera letra en mayúscula
                    }),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state); // Formatear el estado para que se muestre con la primera letra en mayúscula
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->label('Mensaje')
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state); // Formatear el estado para que se muestre con la primera letra en mayúscula
                    })
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Mensaje')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Crear Nuevo Mensaje'),
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
