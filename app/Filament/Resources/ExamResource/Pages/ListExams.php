<?php

namespace App\Filament\Resources\ExamResource\Pages;

use Filament\Actions;
use App\Models\Exam;

use Livewire\Attributes\On;

use App\Filament\Resources\ExamResource;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;


class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    public array $filters = [];

    #[On('updateTableQuery')]
    public function updateTableQuery(array $search): void
    {
        $this->filters = $search;
        $this->resetTable();
    }

    protected function modifyQueryWithActiveTab(Builder $query): Builder
    {
        $query = parent::modifyQueryWithActiveTab($query);
        /* //TODO: Query con select
        $query = $query->select(
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
        */
        $query = $query->leftjoin('mx_patients', 'mx_exams.patient_id', 'mx_patients.id');
        $query = $query->leftjoin('communes', 'mx_exams.comuna', 'communes.code_deis');
        $query = $query->leftjoin('mx_establishments', 'mx_exams.cesfam', 'mx_establishments.new_code_deis');

        if($this->filters)
        {
            if (!empty($this->filters['run'])) {
                $query = $query->where('mx_patients.run', '=', $this->filters['run']);
            }
            if (!empty($this->filters['code_deis'])) {
                $query = $query->where('mx_exams.establecimiento_realiza_examen', '=', $this->filters['code_deis']);
            }
            if (!empty($this->filters['code_deis_request'])) {
                //TODO: Auth::user()->establishment_id
                $query = $query->where('mx_exams.cesfam', '=', $this->filters['code_deis_request']);
            }
            if (!empty($this->filters['commune'])) {
                //TODO: Auth::user()->establishment_id
                $query = $query->where('mx_exams.comuna', '=', $this->filters['commune']);
            }
        }
        else
        {
            $query->whereNull('mx_exams.id');
        }
        return $query;
    }

    protected function getHeaderActions(): array
    {
        date_default_timezone_set('America/Santiago');
        return [

            ExportAction::make()->exports([

                // Excel Export with custom format
                ExcelExport::make('Descargar en Excel')->fromTable()->withColumns([
                    Column::make('servicio_salud')
                        ->heading('S. SALUD'),
                    Column::make('establishmentOrigin.alias')
                        ->heading('CESFAM'),
                    Column::make('profesional_solicita')
                        ->heading('PROFESIONA SOL.'),
                    Column::make('patients.run')
                        ->heading('RUN'),
                        // TODO: Mostrar run,guion y dv
                        // ->formatStateUsing(fn($state, Exam $exam)=>$exam->patients->run . '-' . $exam->patients->dv),
                    Column::make('patients.name')
                        ->heading('NOMBRE'),
                        // TODO: Mostrar Nombre y ambos Apellidos
                        //->formatStateUsing(fn($state, Exam $exam)=>$exam->patients->name . ' ' . $exam->patients->fathers_family . ' ' . $exam->patients->mothers_family),
                    Column::make('patients.gender')
                        ->heading('GENERO')
                        ->formatStateUsing(fn($state)=>($state=='female'?'Femenino':'Masculino')),
                    Column::make('patients.birthday')
                        // TODO: Mostrar solo fecha en formato dmY
                        // ->format(NumberFormat::FORMAT_DATE_DDMMYYYY)
                        // ->formatStateUsing(fn($state)=>($state==\PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(date("d/m/Y", strtotime($state)))))
                        ->heading('F. NAC'),
                    Column::make('patients.age')
                        ->heading('EDAD')
                        ->formatStateUsing(fn($state)=>intval($state)),
                    Column::make('patients.address')
                        ->heading('DIRECCION'),
                    Column::make('establishmentExam.alias')
                        ->heading('EST. EXAMEN'),
                    Column::make('date_exam_order')
                        ->heading('F. ORDEN'),
                    Column::make('date_exam')
                        ->heading('F. EXAMEN'),
                    Column::make('date_exam_reception')
                        ->heading('F. RESULTADO'),
                    Column::make('birards_mamografia')
                        ->heading('MAMOGRAFIA'),
                    Column::make('birards_ecografia')
                        ->heading('ECOGRAFIA'),
                    Column::make('birards_proyeccion')
                        ->heading('PROYECCION'),
                    Column::make('medico')
                        ->heading('MEDICO'),
                ])
                ->withFilename('Patient_History-' . date('dmY_Hs'))
            ])

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExamResource\Widgets\SearchExamWidget::class,
        ];
    }
}
