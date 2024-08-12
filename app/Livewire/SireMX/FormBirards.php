<?php

namespace App\Livewire\SireMx;

use App\Models\Establishment;
use App\Models\Commune;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;


class FormBirards extends Component implements HasForms
{
    // use InteractsWithTable;
    use InteractsWithForms;

    protected int | string | array $columnSpan = 'full';

    public $inicio;

    public $final;

    public $commune;

    public $original_establishment;

    public $exam_establishment;

    public $exam_type;

    public $birards = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('inicio')
                    ->label('Inicio')
                    ->id('inicio')
                    ->hint('Considera fecha de toma de exámen'),
                Forms\Components\DatePicker::make('final')
                    ->label('Fin')
                    ->id('final')
                    ->hint('Considera fecha de toma de exámen'),
                Forms\Components\Select::make('birards')
                    ->label('Birards')
                    ->id('birards')
                    ->hidden(!$this->birards)
                    // ->placeholder('Seleccione')
                    // ->multiple() //FIXME: No funciona bien sin modelo.

                    ->options([
                        '99' => 'Seleccione',
                        '0' => '0',
                        '1' => 'I',
                        '2' => 'II',
                        '3' => 'III',
                        '4' => 'IV',
                        '5' => 'V',
                        '6' => 'VI',
                        '7' => 'VII'
                    ])
                    ->default('99')
                    ->selectablePlaceholder(false),
                Forms\Components\Select::make('commune')
                    ->id('commune')
                    ->label('Comuna')
                    ->placeholder('Seleccione')
                    ->options(
                        Commune::Where('name','LIKE','%%')
                        ->Wherein('region_id',['1'])
                        ->get()
                        ->pluck('name', 'id')
                    ),
                Forms\Components\Select::make('original_establishment')
                    ->id('original_establishment')
                    ->label('Establecimiento Origen')
                    ->placeholder('Seleccione')
                    ->options(
                        Establishment::Where('name','LIKE','%%')
                        //TODO: ->Where('id','LIKE','%'.$idRole.'%')
                        ->Where('exam_emits','LIKE','Y')
                        ->Where('exam_center','LIKE','Y')
                        ->Wherein('commune_id',['5', '6','7', '8','9', '10', '11'])
                        ->orderBy('new_code_deis')
                        ->get()
                        ->pluck('name', 'new_code_deis')
                    ),
                Forms\Components\Select::make('exam_establishment')
                    ->id('exam_establishment')
                    ->label('Establecimiento Toma de Exámen')
                    ->placeholder('Seleccione')
                    ->options(
                        Establishment::Where('name','LIKE','%%')
                        //TODO: ->Where('id','LIKE','%'.$idRole.'%')
                        ->Where('exam_emits','LIKE','Y')
                        ->Where('exam_center','LIKE','Y')
                        ->Wherein('commune_id',['5', '6','7', '8','9', '10', '11'])
                        ->orderBy('new_code_deis')
                        ->get()
                        ->pluck('name', 'new_code_deis')
                    ),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('Buscar')
                        ->action(
                            function (Forms\Get $get, Forms\Set $set) {
                                $inicio = $get('inicio')?$get('inicio'):date("Y-01-01");
                                $set('inicio', $inicio);
                                $final = $get('final')?$get('final'):date("Y-m-d");
                                $set('final', $final);
                                $selected_birards = $get('birards');
                                $code_deis_request = $get('original_establishment')?$get('original_establishment'):'';
                                $code_deis = $get('exam_establishment')?$get('exam_establishment'):'';
                                $commune = $get('commune')?$get('commune'):'';
                                $search['inicio'] = $inicio;
                                $search['final'] = $final;
                                $search['selectedBirards'] = $selected_birards;
                                $search['code_deis_request'] = $code_deis_request;
                                $search['code_deis'] = $code_deis;
                                $search['commune'] = $commune;
                                // dd($search);
                                $this->dispatch('updateFilters', $search);
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

    public function render(): View
    {
        return view('livewire.sire-mx.form-birards');
    }
}
