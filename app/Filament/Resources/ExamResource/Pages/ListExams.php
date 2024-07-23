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
}
