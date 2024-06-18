<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use App\Models\Condition;
use App\Models\Coding;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ConditionList extends Page implements Forms\Contracts\HasForms, Tables\Contracts\HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected static string $view = 'filament.pages.condition-list';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Pacientes con Codición';
    protected static ?string $navigationGroup = 'Usuarios';
    protected static ?string $slug = 'condition-patients';
    
    protected static ?string $title = 'Pacientes con Condición';

    public $conditionTypes = [];
    public $condition_id;

    public function mount()
    {
        $this->conditionTypes = Coding::pluck('display', 'id')->toArray();
        $this->form->fill([
            'condition_id' => null,
        ]);
    }

    protected function getFormSchema(): array
    {
        /*
        return [
            Forms\Components\Select::make('condition_id')
                ->label('Tipo de Condición')
                ->options($this->conditionTypes)
        ];
        */

        return [
            Forms\Components\Select::make('condition_id')
                ->label('Tipo de Condición')
                ->options($this->conditionTypes)
                ->required()
                ->reactive() // Hacer que el select sea reactivo
                ->afterStateUpdated(fn ($state) => $this->updatedConditionId($state)), // Llamar a un método cuando se actualice
        ];
    }

    public function updatedConditionId($conditionId)
    {
        $this->condition_id = $conditionId;
        $this->table->query($this->getTableQuery());
    }

    protected function fillTable()
    {
        // Forzar la recarga de la tabla
        $this->emit('refreshTable');
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
                ->label('N°'),
            Tables\Columns\TextColumn::make('address.location.longitude')
                ->label('Longitud'),
            Tables\Columns\TextColumn::make('address.location.latitude')
                ->label('Latitud'),
            // Agrega más columnas según tus necesidades
        ];
    }
}
