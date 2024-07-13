<?php

namespace App\Filament\Pages;

use App\Models\Commune;
use App\Models\Establishment;
use App\Models\Exam;
use App\Models\Patient;

use Filament\Pages\Page;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Infolists;
use Filament\Infolists\Infolist;

use Illuminate\Support\Facades\DB;

class PatientHistory extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.patient-history';

    // protected $patient;

    // public $rut;

    // public ?array $data = [];



    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('rut')
                    // ->label('RUT')
                    // ->live()
                    // ->required()
                    ->hint('Utilizar formato: 13650969-1'),
                Forms\Components\Select::make('commune')
                    ->label('Comuna')
                    ->placeholder('Seleccione')
                    ->options(Commune::Where('name','LIKE','%%')
                    ->Wherein('region_id',['1'])
                    ->get()
                    ->pluck('name', 'id')),
                Forms\Components\Select::make('original_establishment')
                    ->label('Establecimiento Origen')
                    ->placeholder('Seleccione')
                    ->options(Establishment::Where('name','LIKE','%%')
                        //->Where('id','LIKE','%'.$idRole.'%')
                    ->Where('exam_emits','LIKE','Y')
                    ->Where('exam_center','LIKE','Y')
                    ->Wherein('commune_id',['5', '6','7', '8','9', '10', '11'])
                    ->orderBy('new_code_deis')
                    ->get()
                    ->pluck('name', 'id')),
                Forms\Components\Select::make('exam_establishment')
                    ->label('Establecimiento Toma de Exámen')
                    ->placeholder('Seleccione')
                    ->options(Establishment::Where('name','LIKE','%%')
                    //->Where('id','LIKE','%'.$idRole.'%')
                    ->Where('exam_emits','LIKE','Y')
                    ->Where('exam_center','LIKE','Y')
                    ->Wherein('commune_id',['5', '6','7', '8','9', '10', '11'])
                    ->orderBy('new_code_deis')
                    ->get()
                    ->pluck('name', 'id')),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('Buscar')
                        ->action(function (Forms\Get $get, Forms\Set $set) {
                            $run = str($get('rut'));
                            // dd($this->form->getState());
                            dd($run);
                            $code_deis_request = str($get('original_establishment'))?'':str($get('original_establishment'));
                            $code_deis = str($get('exam_establishment'))?'':str($get('exam_establishment'));
                            $commune = str($get('commune'))?'':str($get('commune'));

                            $data = Exam::select(
                                'mx_patients.run',
                                'mx_patients.dv',
                                'mx_patients.name',
                                'mx_patients.fathers_family',
                                'mx_patients.mothers_family',
                                'mx_patients.gender',
                                'mx_patients.telephone',
                                'mx_patients.birthday',
                                'mx_patients.address',
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
                                'communes.name',
                                'mx_establishments.alias'
                            )
                            ->leftjoin('mx_patients', 'mx_exams.patient_id', 'mx_patients.id')
                            ->leftjoin('communes', 'mx_exams.comuna', 'communes.code_deis')
                            ->leftjoin('mx_establishments', 'mx_exams.cesfam', 'mx_establishments.new_code_deis')
                            ->where('mx_patients.run', '=', $run)
                            // ->where('mx_exams.establecimiento_realiza_examen', '=', $code_deis)
                            // ->where('mx_exams.cesfam', '=', $code_deis_request)
                            // ->where('mx_exams.comuna', '=', $commune)
                            ->get();
                            // dd($data);
                            // return $data;

                            /*
                            $sql="
                                SELECT
                                    T1.run,
                                    T1.dv,
                                    T1.name,
                                    T1.fathers_family,
                                    T1.mothers_family,
                                    T1.gender,
                                    T1.telephone,
                                    DATE_FORMAT(T1.birthday, '%d/%m/%Y') AS birthday,
                                    YEAR(CURDATE())-YEAR(T1.birthday) AS age,
                                    T1.address,
                                    DATE_FORMAT(T0.date_exam_order, '%d/%m/%Y') AS date_exam_order,
                                    DATE_FORMAT(T0.date_exam, '%d/%m/%Y') AS date_exam,
                                    DATE_FORMAT(T0.date_exam_reception, '%d/%m/%Y') AS date_exam_reception,
                                    T0.birards_mamografia,
                                    T0.birards_ecografia,
                                    T0.birards_proyeccion,
                                    T0.diagnostico,
                                    ES2.alias AS establecimiento_realiza_examen,
                                    T0.profesional_solicita,
                                    T0.medico,
                                    T0.servicio_salud,
                                    CO.name AS comuna_name,
                                    ES.alias AS cesfam_name
                                FROM exams T0
                                LEFT JOIN patients T1 ON T0.patient_id = T1.id
                                LEFT JOIN communes CO  ON T0.comuna = CO.code_deis
                                LEFT JOIN establishments ES  ON T0.cesfam = ES.new_code_deis
                                LEFT JOIN establishments ES2  ON T0.establecimiento_realiza_examen = ES2.new_code_deis
                                WHERE T1.run = '".$run."'
                                    ".$code_deis_request ."
                                    ".$code_deis ."
                                    ".$commune."
                                ORDER BY T0.id DESC";

                            $patient = DB::select($sql);
                            */

                        }),
                    Forms\Components\Actions\Action::make('Limpiar')
                        ->action(function () {
                            $this->form->fill();
                    })
                ])
            ])
            ->columns(2);
            // ->statePath('data');
    }


    /*
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('run')
                ->label('RUT'),
                Infolists\Components\TextEntry::make('dv'),
            ]);
    }
    */


}
