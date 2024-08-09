<?php

namespace App\Filament\Pages;

use App\Models\Exam;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

use Illuminate\Database\Eloquent\Builder;

class ReportMx extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.report-mx';

    protected static ?string $navigationLabel = 'Reporte MX';

    protected static ?string $navigationGroup = 'Examenes Mamarios';

    protected static ?string $slug = 'reportMX';

    protected $listeners = ['updateFilters' => 'setFilters'];

    public $filters;

    public function setFilters($filters)
    {
        $this->filters = $filters;
        $this->dispatch('form-submited', $this->filters);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $query = Exam::query();
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
                return $query;
            })
            ->modifyQueryUsing(function (Builder $query) {
                if($this->filters){
                    if(!empty($this->filters['inicio'])){
                        $query->where('mx_exams.date_exam', '>=', $this->filters['inicio']);
                    }
                    if(!empty($this->filters['final'])){
                        $query->where('mx_exams.date_exam', '<=', $this->filters['final']);
                    }
                    if (!empty($this->filters['commune'])) {
                        $query->where('mx_exams.comuna', '=', $this->filters['commune']);
                    }
                    if (!empty($this->filters['code_deis'])) {
                        //TODO: Auth::user()->establishment_id
                        $query->where('mx_exams.establecimiento_realiza_examen', '=', $this->filters['code_deis']);
                    }
                    if (!empty($this->filters['code_deis_request'])) {
                        //TODO: Auth::user()->establishment_id
                        $query->where('mx_exams.cesfam', '=', $this->filters['code_deis_request']);
                    }
                }
                else {
                    $query->whereNull('mx_exams.id');
                }
                return $query;
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
            ->heading('LISTADO DE PACIENTES');
    }
}
