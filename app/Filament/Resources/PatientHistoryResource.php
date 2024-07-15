<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientHistoryResource\Pages;
use App\Filament\Resources\PatientHistoryResource\RelationManagers;

use App\Models\PatientHistory;

use Filament\Forms;
use Filament\Forms\Form;

use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Tables\Filters\Filter;

class PatientHistoryResource extends Resource
{
    protected static ?string $model = PatientHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



    public static function searchPatientHistory(string $run)
    {
        // dd(gettype(self::getModel()));

        patientHistory::searchPatientHistory($run);

    }

    /*
    public static function getRecordTitle(?Model $record): string|null|Htmlable
    {
        return $record->title;
    }
    */

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('servicio_salud'),
                Tables\Columns\TextColumn::make('cesfam_name'),
                Tables\Columns\TextColumn::make('profesional_solicita'),
                Tables\Columns\TextColumn::make('run'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('birthday'),
                Tables\Columns\TextColumn::make('age'),
                Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('establecimiento_realiza_examen'),
                Tables\Columns\TextColumn::make('date_exam_order'),
                Tables\Columns\TextColumn::make('date_exam'),
                Tables\Columns\TextColumn::make('date_exam_reception'),
                Tables\Columns\TextColumn::make('birards_mamografia'),
                Tables\Columns\TextColumn::make('birards_ecografia'),
                Tables\Columns\TextColumn::make('birards_proyeccion'),
                Tables\Columns\TextColumn::make('medico')
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                /*
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                */
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            PatientHistoryResource\Widgets\ListPatientHistoryWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatientHistories::route('/'),
            // 'create' => Pages\CreatePatientHistory::route('/create'),
            // 'edit' => Pages\EditPatientHistory::route('/{record}/edit'),
        ];
    }
}
