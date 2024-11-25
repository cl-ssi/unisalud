<?php

namespace App\Filament\Resources\DependentUserResource\RelationManagers;

use App\Models\Condition;
use App\Models\DependentConditions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'dependentConditions';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('condition_id')
                    ->options(fn(RelationManager $livewire): array => Condition::whereNotIn('id', $livewire->getOwnerRecord()->dependentConditions->pluck('condition_id')->toArray())->pluck('name', 'id')->toArray()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('condition_id')
            ->columns([
                // Tables\Columns\TextColumn::make('condition_id'),
                Tables\Columns\TextColumn::make('condition.name')
                    ->label('Condicion'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                ->label('Eliminar')
                ->recordTitle(fn(Model $record) => $record->condition->name),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados'),
                ]),
            ]);
    }

}
