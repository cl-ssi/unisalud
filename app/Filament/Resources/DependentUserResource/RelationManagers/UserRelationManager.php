<?php

namespace App\Filament\Resources\DependentUserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;


class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'user';

    protected static ?string $title = 'Datos Personales';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('text')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->columns([
                Tables\Columns\TextColumn::make('mobileContactPoint.organization.alias')
                    ->wrap()
                    ->label('Establecimiento'),
                Tables\Columns\TextColumn::make('text')
                    ->wrap()
                    ->label('Nombre Completo'),
                Tables\Columns\TextColumn::make('given')

                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('fathers_family')

                    ->label('Apellido Paterno'),
                Tables\Columns\TextColumn::make('mothers_family')

                    ->label('Apellido Materno'),
                Tables\Columns\TextColumn::make('officialIdentifier.rut')
                    ->label('RUT'),
                Tables\Columns\TextColumn::make('officialIdentifier.value')

                    ->label('RUN'),
                Tables\Columns\TextColumn::make('officialIdentifier.dv')

                    ->label('DV'),
                Tables\Columns\TextColumn::make('dependentUser.healthcare_type')

                    ->label('Prevision'),
                Tables\Columns\TextColumn::make('sex')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Sexo'),
                Tables\Columns\TextColumn::make('gender')

                    ->label('Genero'),
                Tables\Columns\TextColumn::make('birthday')

                    ->label('Fecha Nacimiento')
                    ->date('Y-m-d'),
                Tables\Columns\TextColumn::make('age')
                    ->label('Edad'),
                Tables\Columns\TextColumn::make('nationality.name')

                    ->label('Nacionalidad'),
                Tables\Columns\TextColumn::make('address.full_address')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dirección'),
                Tables\Columns\TextColumn::make('address.text')

                    ->label('Calle'),
                Tables\Columns\TextColumn::make('address.line')

                    ->label('Número'),
                Tables\Columns\TextColumn::make('address.apartment')

                    ->label('Departamento'),
                Tables\Columns\TextColumn::make('address.commune.name')

                    ->label('Comuna'),
                Tables\Columns\TextColumn::make('mobileContactPoint.value')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Telefono'),
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
