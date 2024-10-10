<?php

namespace App\Filament\Resources\WaitlistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\CreateAction;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->placeholder('Selecciona una opción')
                    ->options([
                        'si'            => 'Sí',
                        'no primera'    => 'No (1ra Oportunidad)',
                        'no segunda'    => 'N0 (2da Oportunidad)',
                    ])
                ->required(),
                Forms\Components\DateTimePicker::make('contacted_at')
                    ->label('Fecha/Hora de Contacto')         
                    ->required(), // Formato de la fecha (opcional)
                Forms\Components\Textarea::make('text')
                    ->label('Observación') 
                    ->columnSpan('full') 
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado de Contacto'),
                Tables\Columns\TextColumn::make('contacted_at')
                    ->label('Fecha de Contacto'),
                Tables\Columns\TextColumn::make('text')
                    ->label('Observaciones'),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Registrado Por'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Registro')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Aquí puedes agregar o modificar los datos antes de crear el registro
                        $data['register_user_id'] = auth()->user()->id; // Guardar el ID del usuario autenticado
                        // $data['organization_user_id'] = 123; // Otro dato que no proviene del formulario
                        
                        return $data;
                    }),
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
