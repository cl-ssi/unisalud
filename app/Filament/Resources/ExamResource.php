<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;

use Filament\Resources\Resource;

use App\Models\Exam;
use App\Models\Patient;
use App\Models\Commune;
use App\Models\Establishment;

use Livewire\Attributes\On;

use Filament\Forms;
use Filament\Forms\Form;


use Filament\Tables;
use Filament\Tables\Table;

use Filament\Tables\Filters;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Enums\FiltersLayout;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Historial Paciente';

    protected static ?string $navigationGroup = 'Examenes Mamarios';

    protected static ?string $slug = 'patientHistory';

    // protected static ?string $navigationParentItem = 'Reportes'; // TODO: Clusters Reportes

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
            ->modifyQueryUsing(function ($query) {
                $query->leftjoin('mx_patients', 'mx_exams.patient_id', 'mx_patients.id');
                $query->leftjoin('communes', 'mx_exams.comuna', 'communes.code_deis');
                $query->leftjoin('mx_establishments', 'mx_exams.cesfam', 'mx_establishments.new_code_deis');
                $query->select(
                    'mx_exams.id',
                    'mx_exams.patient_id',
                    'mx_exams.cesfam',
                    'mx_exams.date_exam_order',
                    'mx_exams.date_exam',
                    'mx_exams.date_exam_reception',
                    'mx_exams.birards_mamografia',
                    'mx_exams.birards_ecografia',
                    'mx_exams.birards_proyeccion',
                    'mx_exams.diagnostico',
                    'mx_exams.profesional_solicita',
                    'mx_exams.medico',
                    'mx_exams.servicio_salud',
                    'mx_exams.comuna',
                    'mx_patients.id',
                    'mx_patients.run',
                    'mx_patients.dv',
                    'mx_patients.name',
                    'mx_patients.fathers_family',
                    'mx_patients.mothers_family',
                    'mx_patients.gender',
                    'mx_patients.telephone',
                    'mx_patients.birthday',
                    'mx_patients.address',
                    'communes.name',
                    'communes.code_deis',
                    'mx_establishments.new_code_deis',
                    'mx_establishments.alias'
                );
            })
            ->columns([
                Tables\Columns\TextColumn::make('servicio_salud')
                    ->label('S. SALUD')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('establishmentOrigin.alias')
                    ->label('CESFAM')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('profesional_solicita')
                    ->label('PROFESIONA SOL.')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('patients.run')
                    ->label('RUN')
                    ->placeholder('-')
                    ->formatStateUsing(fn($state, Exam $exam)=>$exam->patients->run . '-' . $exam->patients->dv),
                Tables\Columns\TextColumn::make('patients.name')
                    ->label('NOMBRE')
                    ->placeholder('-')
                    ->formatStateUsing(fn($state, Exam $exam)=>$exam->patients->name . ' ' . $exam->patients->fathers_family . ' ' . $exam->patients->mothers_family),
                Tables\Columns\TextColumn::make('patients.gender')
                    ->label('GENERO')
                    ->placeholder('-')
                    ->formatStateUsing(fn($state)=>($state=='female'?'Femenino':'Masculino')),
                Tables\Columns\TextColumn::make('patients.birthday')
                    ->label('F. NAC')
                    ->date("d/m/Y")
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('patients.age')
                    ->label('EDAD')
                    ->placeholder('-')
                    ->formatStateUsing(fn($state)=>intval($state)),
                Tables\Columns\TextColumn::make('patients.address')
                    ->label('DIRECCION'),
                Tables\Columns\TextColumn::make('establishmentExam.alias')
                    ->label('EST. EXAMEN')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('date_exam_order')
                    ->label('F. ORDEN')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('date_exam')
                    ->label('F. EXAMEN')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('date_exam_reception')
                    ->label('F. RESULTADO')
                    ->placeholder('-'),
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
                    ->label('MEDICO')
                    ->placeholder('-'),
            ])
            ->filters([
                Filters\Filter::make('RUT')
                    ->form([
                        Forms\Components\TextInput::make('rut')
                            ->length(10)
                            ->reactive()
                            ->mask('99999999-*')
                            ->hint('Utilizar formato: 13650969-1'),
                    ])
                    ->modifyQueryUsing(function ($query, array $data) {
                        if($data['rut']){
                            list($run,$dv) = array_pad(explode('-',str_replace(".", "", $data['rut'])),2,null);
                            $query->where('mx_patients.run', '=', $run);
                        } else {
                            $query->where('mx_patients.run', '=', '');
                        }
                        return $query;
                    }),
                Filters\SelectFilter::make('Comuna')
                    ->relationship('commune', 'name', fn (Builder $query) => $query->Wherein('region_id',['1']) )
                    ->placeholder('Seleccione'),
                Filters\SelectFilter::make('Establecimiento Origen')
                    ->relationship('establishmentOrigin', 'name', fn (Builder $query) => $query->Where('exam_emits','LIKE','Y')->Where('exam_center','LIKE','Y')->Wherein('commune_id',['5', '6','7', '8','9', '10', '11'])->orderBy('new_code_deis'))
                    ->placeholder('Seleccione'),
                Filters\SelectFilter::make('Establecimiento Toma de Exámen')
                    ->relationship('establishmentExam', 'name', fn (Builder $query) => $query->Where('exam_emits','LIKE','Y')->Where('exam_center','LIKE','Y')->Wherein('commune_id',['5', '6','7', '8','9', '10', '11'])->orderBy('new_code_deis'))
                    ->placeholder('Seleccione'),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->hiddenFilterIndicators()
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
        ];
    }
}
