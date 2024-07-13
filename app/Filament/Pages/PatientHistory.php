<?php

namespace App\Filament\Pages;

use App\Models\Commune;
use App\Models\Establishment;
use App\Models\Exam;
use App\Models\Patient;

// use \Sushi\Sushi;

use Filament\Pages\Page;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

/*
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
*/

use Filament\Infolists;
use Filament\Infolists\Infolist;

class PatientHistory extends Page implements HasForms //, HasTable
{
    use InteractsWithForms;
    // use InteractsWithTable;
    // use \Sushi\Sushi;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.patient-history';

    // protected static bool $isLazy = false;

    public $patientHistory;

    public $rut;

    public $commune;

    public $original_establishment;

    public $exam_establishment;

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
                            list($run,$dv) = array_pad(explode('-',str_replace(".", "", $run)),2,null);
                            // dd($run);
                            $code_deis_request = str($get('original_establishment'))?'':str($get('original_establishment'));
                            $code_deis = str($get('exam_establishment'))?'':str($get('exam_establishment'));
                            $commune = str($get('commune'))?'':str($get('commune'));


                            $this->patientHistory = Exam::select(
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
                            // return $patientHistory;
                            // dd($this->patientHistory);

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
    public function table(Table $table): Table
    {
        // $temp['patientHistory'] = $this->patientHistory->toArray();
        // $table->state($temp);
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('servicio_salud'),
                Tables\Columns\TextColumn::make('cesfam_name'),
                Tables\Columns\TextColumn::make('profesional_solicita'),
                Tables\Columns\TextColumn::make('run'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('birthday'),
                Tables\Columns\TextColumn::make('age'),
                Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('establecimiento_realiza_examen'),
                Tables\Columns\TextColumn::make('date_exam_order'),
                Tables\Columns\TextColumn::make('date_exam'),
                Tables\Columns\TextColumn::make('date_exam_reception'),
                Tables\Columns\TextColumn::make('birards_mamografia'),
                Tables\Columns\TextColumn::make('birards_ecografia'),
                Tables\Columns\TextColumn::make('birards_proyeccion'),
                Tables\Columns\TextColumn::make('medico'),
            ]);
    }
    */


    public function infolist(Infolist $infolist): Infolist
    {
        if($this->patientHistory == null){
            return $infolist;
        }
        else {


            // $infolist->record($this->patientHistory);
            // $infolist->state('')
            $temp['patientHistory'] = $this->patientHistory->toArray();
            $infolist->state($temp);
            // dd($this->patientHistory->toArray());
            return $infolist
                ->schema([
                    Infolists\Components\RepeatableEntry::make('patientHistory')
                        ->schema([
                            Infolists\Components\TextEntry::make('servicio_salud'),
                            Infolists\Components\TextEntry::make('cesfam_name'),
                            Infolists\Components\TextEntry::make('profesional_solicita'),
                            Infolists\Components\TextEntry::make('run'),
                            Infolists\Components\TextEntry::make('name'),
                            Infolists\Components\TextEntry::make('birthday'),
                            Infolists\Components\TextEntry::make('age'),
                            Infolists\Components\TextEntry::make('address'),
                            Infolists\Components\TextEntry::make('establecimiento_realiza_examen'),
                            Infolists\Components\TextEntry::make('date_exam_order'),
                            Infolists\Components\TextEntry::make('date_exam'),
                            Infolists\Components\TextEntry::make('date_exam_reception'),
                            Infolists\Components\TextEntry::make('birards_mamografia'),
                            Infolists\Components\TextEntry::make('birards_ecografia'),
                            Infolists\Components\TextEntry::make('birards_proyeccion'),
                            Infolists\Components\TextEntry::make('medico'),
                        ])
                ]);
        }
    }

}
