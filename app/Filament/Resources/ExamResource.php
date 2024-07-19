<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;

use Filament\Resources\Resource;

use App\Models\Exam;
use App\Models\Patient;

use Livewire\Attributes\On;

use Filament\Forms;
use Filament\Forms\Form;


use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /*
    public static function getEloquentQuery(): Builder
    {
        // return static::getModel()::query()->where('patients.run', '');
        // return parent::getEloquentQuery()->where('patients.run', '');

        dd(static::getModel()::query());
        return static::getModel()::query()->where('servicio_salud', '');
    }


    public array $filters = [];

    #[On('updateTableQuery')]
    public function updateQuery(array $filters): void
    {
        dd($filters);
        $this->filters = $filters;
        $this->resetTable();
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
                Tables\Columns\TextColumn::make('servicio_salud')
                    ->label('S. SALUD'),
                Tables\Columns\TextColumn::make('establishmentOrigin.alias')
                    ->label('CESFAM'),
                Tables\Columns\TextColumn::make('profesional_solicita')
                    ->label('PROFESIONA SOL.'),
                Tables\Columns\TextColumn::make('patients.run')
                    ->label('RUN')
                    ->formatStateUsing(fn($state, Exam $exam)=>$exam->patients->run . '-' . $exam->patients->dv),
                Tables\Columns\TextColumn::make('patients.name')
                    ->label('NOMBRE')
                    ->formatStateUsing(fn($state, Exam $exam)=>$exam->patients->name . ' ' . $exam->patients->fathers_family . ' ' . $exam->patients->mothers_family),
                Tables\Columns\TextColumn::make('patients.gender')
                    ->label('GENERO')
                    ->formatStateUsing(fn($state)=>($state=='female'?'Femenino':'Masculino')),
                Tables\Columns\TextColumn::make('patients.birthday')
                    ->date("d/m/Y")
                    ->label('F. NAC'),
                Tables\Columns\TextColumn::make('patients.age')
                    ->label('EDAD')
                    ->formatStateUsing(fn($state)=>intval($state)),
                Tables\Columns\TextColumn::make('patients.address')
                    ->label('DIRECCION'),
                Tables\Columns\TextColumn::make('establishmentExam.alias')
                    ->label('EST. EXAMEN'),
                Tables\Columns\TextColumn::make('date_exam_order')
                    ->label('F. ORDEN')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('date_exam')
                    ->label('F. EXAMEN'),
                Tables\Columns\TextColumn::make('date_exam_reception')
                    ->label('F. RESULTADO'),
                Tables\Columns\TextColumn::make('birards_mamografia')
                    ->label('MAMOGRAFIA')
                    ->placeholder('0'),
                Tables\Columns\TextColumn::make('birards_ecografia')
                    ->label('ECOGRAFIA')
                    ->placeholder('0'),
                Tables\Columns\TextColumn::make('birards_proyeccion')
                    ->label('PROYECCION')
                    ->placeholder('0'),
                Tables\Columns\TextColumn::make('medico')
                    ->label('MEDICO'),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ExamResource\Widgets\SearchExamWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
        ];
    }
}
