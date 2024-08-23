<?php

namespace App\Filament\Pages\Condition;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\User;
use App\Models\DependentUser;
use App\Models\DependentConditions;
use App\Models\Condition;

use Illuminate\Database\Eloquent\Builder;
use Filament\Pages\Page;

class DependentUserEdit extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.condition.dependent-user-edit';

    public ?array $data = [];

    public $user_id = null;

    public function mount(): void
    {
        $this->user_id = $this->user_id??request('user_id');
        $this->form->fill(DependentUser::where('user_id', '=', $this->user_id)->firstOrFail()->attributesToArray());
        // dd($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\TextInput::make('diagnosis')
                        ->label('Diagnostico'),
                    Forms\Components\DatePicker::make('check_in_date')
                        ->label('Fecha de Ingreso'),
                    Forms\Components\DatePicker::make('check_out_date')
                        ->label('Fecha de Egreso'),

                ]),
                Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('integral_visits')
                        ->label('Vistas Integrales'),
                    Forms\Components\DatePicker::make('last_integral_visit')
                        ->label('Última Visita Integral'),
                    Forms\Components\TextInput::make('treatment_visits')
                        ->label('Vistas de Tratamiento'),
                    Forms\Components\DatePicker::make('last_treatment_visit')
                        ->label('Última Visita de Tratamiento'),
                    Forms\Components\TextInput::make('barthel')
                        ->label('Barthel'),
                    Forms\Components\TextInput::make('empam')
                        ->label('Emp/Empam'),
                    Forms\Components\Toggle::make('eleam')
                        ->label('Eleam'),
                    Forms\Components\Toggle::make('upp')
                        ->label('UPP'),
                    Forms\Components\Toggle::make('elaborated_plan')
                        ->label('Plan Elaborado'),
                    Forms\Components\Toggle::make('evaluated_plan')
                        ->label('Plan Evaluado'),
                    Forms\Components\TextInput::make('pneumonia')
                        ->label('Neumonia'),
                    Forms\Components\TextInput::make('influenza')
                        ->label('Influenza'),
                    Forms\Components\TextInput::make('covid_19')
                        ->label('Covid-19'),
                    Forms\Components\DatePicker::make('covid_19_date')
                        ->label('Fecha de Covid-19'),
                    Forms\Components\TextInput::make('extra_info')
                        ->label('Otros'),
                    Forms\Components\TextInput::make('tech_aid')
                        ->label('Ayuda Técnica'),
                    Forms\Components\DatePicker::make('tech_aid_date')
                        ->label('Fecha Ayuda Técnica'),
                    Forms\Components\TextInput::make('nutrition_assistance')
                        ->label('Entrega de Alimentación'),
                    Forms\Components\DatePicker::make('nutrition_assistance_date')
                        ->label('Fecha Entrega de Alimentación'),
                    Forms\Components\Toggle::make('flood_zone')
                        ->label('Zona de Inundabilidad'),
                ])
            ])
            ->statePath('data');
    }

    // protected function getTableQuery()
    // {
    //     // Aquí puedes personalizar la consulta según tus necesidades
    //     $user = User::whereHas('dependentUser', function (Builder $query) {
    //         $query->where('user_id', '=', $this->user_id);
    //     })
    //     ->with(['dependentUser']);
    //     return $user;
    // }

    public function prefixArrayKeys($array, $prefix): array
    {
        return array_combine(
            array_map(fn($k) => $prefix . $k, array_keys($array)),
            $array
        );
    }
}
