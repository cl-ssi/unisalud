<?php

namespace App\Filament\Pages\sireMx;

use App\Models\Exam;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;


class ReportMxBirardsYears extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.sire-mx.report-mx-birards-years';

    protected static ?string $navigationGroup = 'Exámenes Mamarios';

    protected static ?string $navigationLabel = 'Antiguedad MX';

    protected static ?string $title = 'Reporte MX por Birads Antiguedad';

    protected static ?string $slug = 'report-mx-birards-years';

    protected static ?int $navigationSort = 5;

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
                $sub =Exam::query();

                $sub->select(
                    'p.patient_id',
                    DB::raw('MAX(date_exam) as ultimo_examen')
                );
                $sub->groupBy('p.patient_id, ultimo_examen')

                // $query = Exam::query();
                // $query->leftjoin('mx_patients', 'mx_exams.patient_id', 'mx_patients.id');
                // $query->leftjoin('communes', 'mx_exams.comuna', 'communes.code_deis');
                // $query->leftjoin('mx_establishments', 'mx_exams.cesfam', 'mx_establishments.new_code_deis');
                // $query->select(
                //     'mx_exams.id',
                //     'mx_exams.patient_id',
                //     'mx_exams.cesfam',
                //     'mx_exams.date_exam_order',
                //     'mx_exams.date_exam',
                //     'mx_exams.date_exam_reception',
                //     'mx_exams.birards_mamografia',
                //     'mx_exams.birards_ecografia',
                //     'mx_exams.birards_proyeccion',
                //     'mx_exams.diagnostico',
                //     'mx_exams.profesional_solicita',
                //     'mx_exams.medico',
                //     'mx_exams.servicio_salud',
                //     'mx_exams.comuna',
                //     DB::raw('MAX(date_exam) as ultimo_examen, p.patient_id GROUP By p.patient_id FROM exams'),
                //     'mx_patients.id',
                //     'mx_patients.run',
                //     'mx_patients.dv',
                //     'mx_patients.name',
                //     'mx_patients.fathers_family',
                //     'mx_patients.mothers_family',
                //     'mx_patients.gender',
                //     'mx_patients.telephone',
                //     'mx_patients.birthday',
                //     'mx_patients.address',
                //     'communes.name',
                //     'communes.code_deis',
                //     'mx_establishments.new_code_deis',
                //     'mx_establishments.alias'
                // );
                return $query;
            })
            ->modifyQueryUsing(function (Builder $query) {
                if(empty($this->filters)){
                    $query->whereNull('mx_exams.id');
                }
                else {
                    if(!empty($this->filters['year'])){
                        //FIXME: Unknown column 'ultimo_examen'
                        // $query->dd();
                        $query->where('ultimo_examen', '>=', $this->filters['year']);
                    }
                    if(!empty($this->filters['exams'])){
                        $query->where('mx_exams.exam_type', '=', $this->filters['exams']);
                    }
                    if (!empty($this->filters['birard'])) {
                        $query->where('mx_exams.birards_mamografia', '=', $this->filters['birard']);
                    }
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
    protected function getHeaderActions(): array
    {
        date_default_timezone_set('America/Santiago');
        return [

            ExportAction::make()->exports([

                // Excel Export with custom format
                ExcelExport::make('Descargar en Excel')->fromTable()->withColumns([
                    FilamentExcel\Columns\Column::make('servicio_salud')
                        ->heading('S. SALUD'),
                    FilamentExcel\Columns\Column::make('establishmentOrigin.alias')
                        ->heading('CESFAM'),
                    FilamentExcel\Columns\Column::make('profesional_solicita')
                        ->heading('PROFESIONA SOL.'),
                    FilamentExcel\Columns\Column::make('patients.run')
                        ->heading('RUN'),
                        // TODO: Mostrar run,guion y dv
                        // ->formatStateUsing(fn($state, Exam $exam)=>$exam->patients->run . '-' . $exam->patients->dv),
                    FilamentExcel\Columns\Column::make('patients.name')
                        ->heading('NOMBRE'),
                        // TODO: Mostrar Nombre y ambos Apellidos
                        //->formatStateUsing(fn($state, Exam $exam)=>$exam->patients->name . ' ' . $exam->patients->fathers_family . ' ' . $exam->patients->mothers_family),
                    FilamentExcel\Columns\Column::make('patients.gender')
                        ->heading('GENERO')
                        ->formatStateUsing(fn($state)=>($state=='female'?'Femenino':'Masculino')),
                    FilamentExcel\Columns\Column::make('patients.birthday')
                        // TODO: Mostrar solo fecha en formato dmY
                        // ->format(NumberFormat::FORMAT_DATE_DDMMYYYY)
                        // ->formatStateUsing(fn($state)=>($state==\PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(date("d/m/Y", strtotime($state)))))
                        ->heading('F. NAC'),
                    FilamentExcel\Columns\Column::make('patients.age')
                        ->heading('EDAD')
                        ->formatStateUsing(fn($state)=>intval($state)),
                    FilamentExcel\Columns\Column::make('patients.address')
                        ->heading('DIRECCION'),
                    FilamentExcel\Columns\Column::make('establishmentExam.alias')
                        ->heading('EST. EXAMEN'),
                    FilamentExcel\Columns\Column::make('date_exam_order')
                        ->heading('F. ORDEN'),
                    FilamentExcel\Columns\Column::make('date_exam')
                        ->heading('F. EXAMEN'),
                    FilamentExcel\Columns\Column::make('date_exam_reception')
                        ->heading('F. RESULTADO'),
                    FilamentExcel\Columns\Column::make('birards_mamografia')
                        ->heading('MAMOGRAFIA'),
                    FilamentExcel\Columns\Column::make('birards_ecografia')
                        ->heading('ECOGRAFIA'),
                    FilamentExcel\Columns\Column::make('birards_proyeccion')
                        ->heading('PROYECCION'),
                    FilamentExcel\Columns\Column::make('medico')
                        ->heading('MEDICO'),
                ])
                ->withFilename('ReportMxBirardsYears-' . date('dmY_Hs'))
            ])
        ];
    }



}
