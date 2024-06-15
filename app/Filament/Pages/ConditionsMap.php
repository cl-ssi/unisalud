<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use Illuminate\Http\Request;
use Filament\Forms;
use App\Models\Coding;
use Illuminate\Contracts\View\View;

class ConditionsMap extends Page
{
    protected static ?string $navigationGroup = 'Usuarios';
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static string $view = 'filament.pages.conditions-map';

    public static function getNavigationLabel(): string
    {
        return 'Dependientes';
    }

    public $conditionType;
    public $users = [];

    public function mount(Request $request)
    {
        $this->conditionType = $request->input('conditionType', null);
        
        $this->users = User::whereHas('conditions')->get();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('conditionType')
                ->label('Selecciona el tipo de condición')
                ->options(Coding::all()->pluck('display', 'id'))
                ->reactive()
                ->afterStateUpdated(fn ($state, $component) => $this->reloadUsers($state)),
        ];
    }

    protected function reloadUsers($conditionType)
    {
        $this->conditionType = $conditionType;

        if ($this->conditionType) {
            $this->users = User::whereHas('conditions', function($query) use($conditionType) {
                    $query->where('cod_con_code_id', $conditionType);
                })
                ->get();
        } else {
            $this->users = [];
        }
    }

    public function render(): View
    {
        /*
        return view(static::$view, [
            'users' => $this->users,
            'conditionType' => $this->conditionType,
            'form' => $this->form->render()->extends('components.layouts.app'),
        ]);
        */

        return view('filament.pages.conditions-map', [
            'users' => $this->users,
            'conditionType' => $this->conditionType,
            'form' => $this->form->render(),
        ]);
    }
}
