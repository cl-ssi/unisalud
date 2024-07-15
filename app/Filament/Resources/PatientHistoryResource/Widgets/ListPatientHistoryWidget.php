<?php

namespace App\Filament\Resources\PatientHistoryResource\Widgets;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Widgets\Widget;

use app\Filament\Resources\PatientHistoryResource;

use App\Models\Establishment;
use App\Models\Commune;

class ListPatientHistoryWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.resources.patient-history-resource.widgets.list-patient-history-widget';

    protected int | string | array $columnSpan = 'full';

    public $rut;

    public $commune;

    public $original_establishment;

    public $exam_establishment;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('rut')
                    ->label('RUT')
                    ->id('rut')
                    ->required()
                    ->hint('Utilizar formato: 13650969-1'),
                Forms\Components\Select::make('commune')
                    ->label('Comuna')
                    ->placeholder('Seleccione')
                    ->options(
                        Commune::Where('name','LIKE','%%')
                        ->Wherein('region_id',['1'])
                        ->get()
                        ->pluck('name', 'id')
                    ),
                Forms\Components\Select::make('original_establishment')
                    ->label('Establecimiento Origen')
                    ->placeholder('Seleccione')
                    ->options(
                        Establishment::Where('name','LIKE','%%')
                        //->Where('id','LIKE','%'.$idRole.'%')
                        ->Where('exam_emits','LIKE','Y')
                        ->Where('exam_center','LIKE','Y')
                        ->Wherein('commune_id',['5', '6','7', '8','9', '10', '11'])
                        ->orderBy('new_code_deis')
                        ->get()
                        ->pluck('name', 'id')
                    ),
                Forms\Components\Select::make('exam_establishment')
                    ->label('Establecimiento Toma de Exámen')
                    ->placeholder('Seleccione')
                    ->options(
                        Establishment::Where('name','LIKE','%%')
                        //->Where('id','LIKE','%'.$idRole.'%')
                        ->Where('exam_emits','LIKE','Y')
                        ->Where('exam_center','LIKE','Y')
                        ->Wherein('commune_id',['5', '6','7', '8','9', '10', '11'])
                        ->orderBy('new_code_deis')
                        ->get()
                        ->pluck('name', 'id')
                    ),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('Buscar')
                        ->action(
                            function (Forms\Get $get, Forms\Set $set) {
                                $run = str($get('rut'));
                                list($run,$dv) = array_pad(explode('-',str_replace(".", "", $run)),2,null);
                                $code_deis_request = str($get('original_establishment'))?'':str($get('original_establishment'));
                                $code_deis = str($get('exam_establishment'))?'':str($get('exam_establishment'));
                                $commune = str($get('commune'))?'':str($get('commune'));
                                PatientHistoryResource::searchPatientHistory($run);
                                // PatientHistory::searchPatientHistory($run);
                            }
                        ),
                    Forms\Components\Actions\Action::make('Limpiar')
                        ->action(function () {
                            $this->form->fill();
                    })
                ])
            ])
            // ->statePath('data')
            ->columns(2);
    }
}
