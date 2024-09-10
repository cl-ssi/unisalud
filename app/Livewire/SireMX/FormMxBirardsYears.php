<?php

namespace App\Livewire\SireMX;


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

class FormMXBirardsYears extends Component implements HasForms
{
    // use InteractsWithTable;
    use InteractsWithForms;

    protected int | string | array $columnSpan = 'full';

    public $year;

    public $exam_type;

    public $birard;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('year')
                    ->label('Años de Antiguedad')
                    ->id('year')
                    ->selectablePlaceholder(false)
                    ->default(1)
                    ->options([
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                    ]),
                Forms\Components\Select::make('exam_type')
                    ->label('Tipo Exámen')
                    ->id('exam_type')
                    ->placeholder('Seleccione')
                    ->options([
                        'mam' => 'Mamografía',
                        'eco' => 'Ecografía',
                        'proy' => 'Proyección',
                    ]),
                Forms\Components\Select::make('birard')
                    ->label('Birards')
                    ->id('birard')
                    ->placeholder('Seleccione')
                    ->options([
                        '0' => '0',
                        '1' => 'I',
                        '2' => 'II',
                        '3' => 'III',
                        '4' => 'IV',
                        '5' => 'V',
                        '6' => 'VI'
                    ]),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('Buscar')
                        ->action(
                            function (Forms\Get $get, Forms\Set $set) {
                                $year = $get('year');
                                $exam = $get('exam_type');
                                $birard = $get('birard');

                                $search['year'] = $year;
                                $search['exam'] = $exam;
                                $search['birard'] = $birard;
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


    public function render()
    {
        return view('livewire.sire-mx.form-mx-birards-years');
    }
}
