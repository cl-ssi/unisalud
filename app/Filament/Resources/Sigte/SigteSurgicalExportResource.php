<?php

namespace App\Filament\Resources\Sigte;

use App\Filament\Resources\Sigte\SigteSurgicalExportResource\Pages;
use App\Filament\Resources\Sigte\SigteSurgicalExportResource\RelationManagers\WaitlistEntriesRelationManager;
use App\Models\SigteSurgicalExportBatch;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SigteSurgicalExportResource extends Resource
{
    protected static ?string $model = SigteSurgicalExportBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'SIGTE';

    protected static ?string $navigationLabel = 'Exportaciones';

    protected static ?string $label = 'Exportación';

    protected static ?string $pluralLabel = 'Exportaciones';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()->can('SIGTE LE QX: administrador')
            || auth()->user()->can('be god');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('created_at')
                ->label('Fecha')
                ->dateTime('d-m-Y H:i'),
            TextEntry::make('exportedBy.text')
                ->label('Descargado por'),
            TextEntry::make('desde')
                ->label('Desde (F. Entrada)')
                ->date('d-m-Y')
                ->placeholder('-'),
            TextEntry::make('hasta')
                ->label('Hasta (F. Entrada)')
                ->date('d-m-Y')
                ->placeholder('-'),
            TextEntry::make('requestingProfessional.text')
                ->label('Cirujano')
                ->placeholder('-'),
            TextEntry::make('status')
                ->label('Estado')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'completo'   => 'Completo',
                    'incompleto' => 'Incompleto',
                    default      => '-',
                }),
            TextEntry::make('complexity')
                ->label('Complejidad')
                ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '-'),
            TextEntry::make('patients_count')
                ->label('Pacientes exportados'),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('exportedBy.text')
                    ->label('Descargado por')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desde')
                    ->label('Desde')
                    ->date('d-m-Y')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('hasta')
                    ->label('Hasta')
                    ->date('d-m-Y')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('requestingProfessional.text')
                    ->label('Cirujano')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'completo'   => 'Completo',
                        'incompleto' => 'Incompleto',
                        default      => '-',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('complexity')
                    ->label('Complejidad')
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('patients_count')
                    ->label('Pacientes')
                    ->badge()
                    ->color('primary'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            WaitlistEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSigteSurgicalExports::route('/'),
            'view'  => Pages\ViewSigteSurgicalExport::route('/{record}'),
        ];
    }
}
