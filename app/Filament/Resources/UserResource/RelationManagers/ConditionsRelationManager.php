<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enums\ConditionClinicalStatus;
use App\Enums\ConditionVerificationStatus;
use App\Models\Coding;

class ConditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'conditions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('identifier')
                    ->label('Identificador')
                    ->maxLength(255),
                Forms\Components\Select::make('cod_con_clinical_status')
                    ->label('Estado Clínico')
                    ->options(ConditionClinicalStatus::class),
                Forms\Components\Select::make('cod_con_verification_status')
                    ->label('Estado Confirmado')
                    ->options(ConditionVerificationStatus::class),
                Forms\Components\Select::make('cod_con_code_id')
                    ->label('Condición')
                    ->options(Coding::all()->pluck('display', 'id')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cod_con_clinical_status')
                    ->label('Estado Clínico'),
                Tables\Columns\TextColumn::make('cod_con_verification_status')
                    ->label('Estado Confirmación'),
                Tables\Columns\TextColumn::make('coding.display')
                    ->label('Condición')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->modalHeading('Condición de Paciente'),
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
