<?php

namespace App\Filament\Resources\Sigte\SigteSurgicalExportResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WaitlistEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'waitlistEntries';

    protected static ?string $title = 'Pacientes Exportados';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('identifier')
            ->columns([
                Tables\Columns\TextColumn::make('identifier')
                    ->label('ID Local')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('user.officialIdentifier.value')
                    ->label('RUT'),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('suspected_diagnosis')
                    ->label('Patología')
                    ->wrap(),
                Tables\Columns\TextColumn::make('requestingProfessional.text')
                    ->label('Cirujano'),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('F. Entrada')
                    ->date('d-m-Y'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
