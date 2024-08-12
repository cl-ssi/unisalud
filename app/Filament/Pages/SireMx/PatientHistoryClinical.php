<?php

namespace App\Filament\Pages\SireMx;

use Filament\Pages\Page;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

use Filament\Tables\Filters;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Support\Enums\Alignment;

use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel;

class PatientHistoryClinical extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.sire-mx.patient-history-clinical';

    protected static ?string $navigationGroup = 'Exámenes Mamarios';

    protected static ?string $navigationLabel = 'Cartola Paciente';

    protected static ?string $slug = 'patientHistoryClinical';

    protected static ?string $title = 'Cartola Paciente';

    protected static ?int $navigationSort = 1;

    public static function downloadExamById(Exam $exam): Storage
    {
        dd($exam);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $query = Exam::query();
                $query->leftjoin('mx_patients', 'mx_exams.patient_id', 'mx_patients.id');
                $query->select(
                    'mx_exams.id',
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
                    'mx_exams.path',
                    'mx_exams.patient_id',
                    'mx_patients.run',
                    'mx_patients.dv',
                    'mx_patients.name',
                    'mx_patients.fathers_family',
                    'mx_patients.mothers_family',
                    'mx_patients.gender',
                    'mx_patients.telephone',
                    'mx_patients.birthday',
                    'mx_patients.address',
                );
                $query->orderBy('id', 'DESC');
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->placeholder('-'),
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
                            ->minLength(9)
                            ->tel()
                            ->telRegex('^[1-9]\d*\-(\d|k|K)$')
                            ->hint('Utilizar formato: 13650969-1'),
                    ])
                    ->modifyQueryUsing(function ($query, array $data) {
                        if(strlen($data['rut']) >= 9 ){
                            list($run,$dv) = array_pad(explode('-',str_replace(".", "", $data['rut'])),2,null);
                            $query->where('mx_patients.run', '=', $run);
                        } else {
                            $query->where('mx_patients.run', '=', '');
                        }
                        return $query;
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->hiddenFilterIndicators()
            ->actions([
                Tables\Actions\Action::make('path')
                    ->label('REPORTE')
                    ->action(function ($record) {
                        // FIXME: Add GSC de SireMX a el .env
                        // return Storage::disk('gcs')->response($record->path, mb_convert_encoding($record->filename,'ASCII'));
                })
            ])
            ->bulkActions([
            ]);
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
                ->withFilename('Patient_History-Clinical-' . date('dmY_Hs'))
            ])
        ];
    }
}
