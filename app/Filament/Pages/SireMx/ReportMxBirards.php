<?php

namespace App\Filament\Pages\SireMx;

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

use Illuminate\Database\Eloquent\Builder;

class ReportMxBirards extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.sire-mx.report-mx-birards';

    protected static ?string $navigationGroup = 'Exámenes Mamarios';

    protected static ?string $navigationLabel = 'Reporte por Bidards';

    protected static ?string $title = 'Reporte MX por Bidards';

    protected static ?int $navigationSort = 4;

    protected $listeners = ['updateFilters' => 'setFilters'];

    public $filters;

    public static function canAccess(): bool
    {   
        if(auth()->user()->can('be god')){
            return true;
        }
        return auth()->user()->can('SireMx: Manager');
    }

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
                if(empty($this->filters) || $this->filters['selectedBirards'] == '99'){
                    $query->whereNull('mx_exams.id');
                }
                else {
                    // dd($this->filters['birards']);
                    if(!empty($this->filters['inicio'])){
                        $query->where('mx_exams.date_exam', '>=', $this->filters['inicio']);
                    }
                    if(!empty($this->filters['final'])){
                        $query->where('mx_exams.date_exam', '<=', $this->filters['final']);
                    }
                    if (!empty($this->filters['selectedBirards'])) {
                        //TODO: falta seleccion multiple con where IN ()
                        $query->where('mx_exams.birards_mamografia', '=', $this->filters['selectedBirards']);
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
                ->withFilename('ReportBirards-' . date('dmY_Hs'))
            ])
        ];
    }
}
