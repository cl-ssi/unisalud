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
        // dd($this->prefixArrayKeys(User::find($this->user_id)->dependentUser->attributesToArray(), 'dependentUser.'));
        // dd(User::find($this->user_id)->dependentUser->attributesToArray());
        $this->form->fill($this->prefixArrayKeys(User::find($this->user_id)->dependentUser->attributesToArray(), 'dependentUser.'));
        $this->form->fill(User::find($this->user_id)->dependentUser->attributesToArray());
        // dd($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('dependentUser.diagnosis')
                    ->id('diagnosis')
                    ->label('Diagnostico'),
                Forms\Components\DatePicker::make('dependentUser.check_in_date')
                    ->label('Fecha de Ingreso'),
                Forms\Components\DatePicker::make('dependentUser.check_out_date')
                    ->label('Fecha de Egreso'),
                Forms\Components\Toggle::make('dependentUser.integral_visits')
                    ->label('Vistas Integrales'),
                Forms\Components\DatePicker::make('dependentUser.last_integral_visits')
                    ->label('Última Visita Integral'),
                Forms\Components\Toggle::make('dependentUser.treatment_visits')
                    ->label('Vistas de Tratamiento'),
                Forms\Components\DatePicker::make('dependentUser.last_treatment_visits')
                    ->label('Última Visita de Tratamiento'),
                Forms\Components\TextInput::make('dependentUser.barthel')
                    ->label('Barthel'),
                Forms\Components\TextInput::make('dependentUser.empam')
                    ->label('Emp/Empam'),
                Forms\Components\Toggle::make('dependentUser.eleam')
                    ->label('Eleam'),
                Forms\Components\Toggle::make('dependentUser.upp')
                    ->label('UPP'),
                Forms\Components\Toggle::make('dependentUser.elaborated_plan')
                    ->label('Plan Elaborado'),
                Forms\Components\Toggle::make('dependentUser.evaluated_plan')
                    ->label('Plan Evaluado'),
                Forms\Components\TextInput::make('dependentUser.pneumonia')
                    ->label('Neumonia'),
                Forms\Components\TextInput::make('dependentUser.influenza')
                    ->label('Influenza'),
                Forms\Components\TextInput::make('dependentUser.covid_19')
                    ->label('Covid-19'),
                Forms\Components\DatePicker::make('dependentUser.covid_19_date')
                    ->label('Fecha de Covid-19'),
                Forms\Components\TextInput::make('dependentUser.extra_info')
                    ->label('Otros'),
                Forms\Components\TextInput::make('dependentUser.tech_aid')
                    ->label('Ayuda Técnica'),
                Forms\Components\DatePicker::make('dependentUser.tech_aid_date')
                    ->label('Fecha Ayuda Técnica'),
                Forms\Components\TextInput::make('dependentUser.nutrition_assistance')
                    ->label('Entrega de Alimentación'),
                Forms\Components\DatePicker::make('dependentUser.nutrition_assistance_date')
                    ->label('Fecha Entrega de Alimentación'),
                Forms\Components\Toggle::make('dependentUser.flood_zone')
                    ->label('Zona de Inundabilidad'),
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
