<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
// use Filament\Forms;

use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;
use App\Models\Condition;
use App\Models\Coding;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

use Cheesegrits\FilamentGoogleMaps\Fields\Map;

class ConditionList extends Page implements Forms\Contracts\HasForms, Tables\Contracts\HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected static string $view = 'filament.pages.condition-list';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Listado Pacientes con Condición';
    protected static ?string $navigationGroup = 'Usuarios';
    protected static ?string $slug = 'condition-patients';

    protected static ?string $title = 'Listado de Pacientes con Condición';

    public $conditionTypes = [];
    public $condition_id;

    public function mount()
    {
        // $this->conditionTypes = Coding::pluck('display', 'id')->toArray();
        $this->conditionTypes = Coding::pluck('display', 'id')->toArray();
        $this->form->fill([
            'condition_id' => null,
        ]);
    }

    /*
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('condition_id')
                ->label('Tipo de Condición')
                ->options($this->conditionTypes)
                ->required()
                ->reactive() // Hacer que el select sea reactivo
                ->afterStateUpdated(fn ($state) => $this->updatedConditionId($state)), // Llamar a un método cuando se actualice
        ];
    }
        */

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('condition_id')
                    ->label('Tipo de Condición')
                    ->options($this->conditionTypes)
                    ->required()
                    ->reactive() // Hacer que el select sea reactivo
                    ->afterStateUpdated(fn ($state) => $this->updatedConditionId($state)), // Llamar a un método cuando se actualice
            ]);
    }

    public function updatedConditionId($conditionId)
    {
        $this->condition_id = $conditionId;
        $this->table->query($this->getTableQuery());
    }

    protected function getTableQuery(): Builder
    {
        // Aquí puedes personalizar la consulta según tus necesidades
        $usersWithConditions = User::whereHas('conditions', function (Builder $query) {
                $query->where('cod_con_code_id', $this->condition_id);
            });
        return $usersWithConditions;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('text')
                ->label('Nombre Completo'),
            Tables\Columns\TextColumn::make('sex')
                ->label('Sexo'),
            Tables\Columns\TextColumn::make('gender')
                ->label('Genero'),
            Tables\Columns\TextColumn::make('birthday')
                ->label('Fecha Nacimiento')
                ->date(),
            Tables\Columns\TextColumn::make('age')
                ->label('Edad')
                ->getStateUsing(function ($record) {
                    return Carbon::parse($record->birthday)->age;
                }),
            Tables\Columns\TextColumn::make('birthday')
                ->label('Fecha Nacimiento')
                ->date(),
            Tables\Columns\TextColumn::make('conditions.created_at')
                ->label('ingresado')
                ->date(),
            Tables\Columns\TextColumn::make('address.use')
                ->label('Tipo Dirección'),
            Tables\Columns\TextColumn::make('address.text')
                ->label('Calle'),
            Tables\Columns\TextColumn::make('address.line')
                ->label('N°'),
            Tables\Columns\TextColumn::make('address.commune.name')
                ->label('Comuna'),
            Tables\Columns\TextColumn::make('address.location.longitude')
                ->label('Longitud'),
            Tables\Columns\TextColumn::make('address.location.latitude')
                ->label('Latitud'),
            Tables\Columns\TextColumn::make('conditions.diagnosis')
                ->label('Diagnostico'),
            Tables\Columns\TextColumn::make('conditions.check_in_date')
                ->label('Fecha de Ingreso')
                ->date(),
            Tables\Columns\TextColumn::make('conditions.check_out_date')
                ->label('Fecha de Egreso')
                ->date(),
            Tables\Columns\TextColumn::make('conditions.integral_visits')
                ->label('Vistas Integrales'),
            Tables\Columns\TextColumn::make('conditions.last_integral_visit')
                ->label('Última Visita Integral')
                ->date(),
            Tables\Columns\TextColumn::make('conditions.treatment_visits')
                ->label('Visitas de Tratamiento'),
            Tables\Columns\TextColumn::make('conditions.last_treatment_visit')
                ->label('Última Visita de Tratamiento')
                ->date(),
            Tables\Columns\TextColumn::make('conditions.barthel')
                ->label('Barthel'),
            Tables\Columns\TextColumn::make('conditions.empam')
                ->label('Emp / Empam'),
            Tables\Columns\TextColumn::make('conditions.eleam')
                ->label('Eleam')
                ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
            Tables\Columns\TextColumn::make('conditions.upp')
                ->label('UPP')
                ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
            Tables\Columns\TextColumn::make('conditions.elaborated_plan')
                ->label('Plan Elaborado')
                ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
            Tables\Columns\TextColumn::make('conditions.evaluated_plan')
                ->label('Plan Evaluado')
                ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
            Tables\Columns\TextColumn::make('conditions.pneumonia')
                ->label('Neumonia'),
            Tables\Columns\TextColumn::make('conditions.influenza')
                ->label('Influenza'),
            Tables\Columns\TextColumn::make('conditions.covid_19')
                ->label('Covid-19'),
            Tables\Columns\TextColumn::make('conditions.covid_19_date')
                ->label('Fecha de Covid-19')
                ->date(),
            Tables\Columns\TextColumn::make('conditions.extra_info')
                ->label('Otros'),
            Tables\Columns\TextColumn::make('conditions.tech_aid')
                ->label('Ayuda Técnica')
                ->placeholder('No Aplica')
                ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
            Tables\Columns\TextColumn::make('conditions.tech_aid_date')
                ->label('Fecha Ayuda Técnica')
                ->date(),
            Tables\Columns\TextColumn::make('conditions.nutrition_assistance')
                ->label('Entrega de Alimentación')
                ->placeholder('No Aplica')
                ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
            Tables\Columns\TextColumn::make('conditions.nutrition_assistance_date')
                ->label('Fecha Entrega de Alimentación')
                ->date(),
            Tables\Columns\TextColumn::make('conditions.flood_zone')
                ->label('Zona de Inundabilidad')
                ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
            // Agrega más columnas según tus necesidades
        ];
    }
}
