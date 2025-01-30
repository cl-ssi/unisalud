<?php

namespace App\Filament\Pages\SireMx;

use App\Models\Exam;
use App\Models\Patient;

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

    protected static ?int $navigationSort = 5;

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
                // return Patient::with('exams')->query();
                // return Patient::query()->with('exams');
                // return Patient::has('exams');
                $query = Patient::query();
                $query->leftjoin('mx_exams', 'mx_patients.id', 'mx_exams.patient_id');
                // $query->leftjoin('communes', 'mx_exams.comuna', 'communes.code_deis');
                $query->select(
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
                    // 'mx_exams.date_exam',
                    DB::raw('MAX(mx_exams.birards_mamografia) mam'),
                    DB::raw('MAX(mx_exams.birards_ecografia) eco'),
                    DB::raw('MAX(mx_exams.birards_proyeccion) proy'),
                    DB::raw('MAX(mx_exams.date_exam) last_exam'),
                    // 'communes.name',
                    // 'communes.code_deis',
                );
                $query->groupBy(
                    'mx_patients.id',
                    // 'mx_exams.birards_mamografia',
                    // 'mx_exams.birards_ecografia',
                    // 'mx_exams.birards_proyeccion',
                );
                return $query;
            })
            ->modifyQueryUsing(function (Builder $query) {
                if (!empty($this->filters['birard']) && !empty($this->filters['year'])) {
                    $query->whereRaw('TIMESTAMPDIFF(Month, last_exam, NOW()) >= ?', $this->filters['year']);
                    if(empty($this->filters['exam'])){
                        $query->where('mx_exams.birards_mamografia', '=', $this->filters['birard'])
                        ->orWhere('mx_exams.birards_ecografia', '=', $this->filters['birard'])
                        ->orWhere('mx_exams.birards_proyeccion', '=', $this->filters['birard']);
                    } else if($this->filters['exam'] == 'mam'){
                        $query->where('mx_exams.birards_mamografia', '=', $this->filters['birard']);
                    } else if($this->filters['exam'] == 'eco'){
                        $query->where('mx_exams.birards_ecografia', '=', $this->filters['birard']);
                    } else if($this->filters['exam'] == 'proy'){
                        $query->where('mx_exams.birards_proyeccion', '=', $this->filters['birard']);
                    }
                }
                else {
                    $query->whereNull('mx_patients.id');
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('run')
                    ->label('RUN')
                    ->placeholder('-')
                    ->formatStateUsing(fn(Patient $record)=>$record->run . '-' . $record->dv),
                Tables\Columns\TextColumn::make('name')
                    ->label('NOMBRE')
                    ->placeholder('-')
                    ->formatStateUsing(fn(Patient $record)=>$record->name . ' ' . $record->fathers_family . ' ' . $record->mothers_family),
                Tables\Columns\TextColumn::make('gender')
                    ->label('GENERO')
                    ->placeholder('-')
                    ->formatStateUsing(fn($state)=>($state=='female'?'Femenino':'Masculino')),
                Tables\Columns\TextColumn::make('birthday')
                    ->label('F. NAC')
                    ->date("d/m/Y")
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('age')
                    ->label('EDAD')
                    ->placeholder('-')
                    ->formatStateUsing(fn($state)=>intval($state)),
                Tables\Columns\TextColumn::make('address')
                    ->label('DIRECCION'),
                // Tables\Columns\TextColumn::make('communes.name')
                //     ->label('COMUNA'),
                Tables\Columns\TextColumn::make('telephone')
                    ->label('TELEFONO'),
                Tables\Columns\TextColumn::make('last_exam')
                    ->label('F. EXAMEN')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('mam')
                    // ->formatStateUsing(fn(Patient $record)=>$record->exams->first()->birards_mamografia)
                    ->label('MAMOGRAFIA')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('eco')
                    // ->formatStateUsing(fn(Patient $record)=>)
                    ->label('ECOGRAFIA')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('proy')
                    // ->formatStateUsing(fn(Patient $record)=>$record->exams->first()->birards_proyeccion)
                    ->label('PROYECCION')
                    ->placeholder('-'),
            ])
            ->heading('LISTADO DE PACIENTES');
    }
    protected function getHeaderActions(): array
    {
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
