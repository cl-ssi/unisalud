<?php

namespace App\Filament\Pages\Condition;

use App\Models\User;
use App\Models\DependentUser;
use App\Models\DependentConditions;
use App\Models\Condition;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Forms;
use Filament\Actions;

use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel;

use Cheesegrits\FilamentGoogleMaps\Fields\Map;

class DependentUserList extends Page implements Forms\Contracts\HasForms, Tables\Contracts\HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected static string $view = 'filament.pages.condition.dependent-user-list';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Listado Pacientes con Condición';
    protected static ?string $navigationGroup = 'Usuarios';
    // protected static ?string $slug = 'condition-patients';

    protected static ?string $title = 'Listado de Pacientes con Condición';

    // public $conditionTypes = [];

    public $condition_id;

    public function mount()
    {
        // $this->conditionTypes = Condition::pluck('name', 'id')->toArray();
        // $this->form->fill([
        //     'condition_id' => null,
        // ]);
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

    // public function form(Forms\Form $form): Forms\Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\Select::make('condition_id')
    //                 ->label('Tipo de Condición')
    //                 ->options($this->conditionTypes)
    //                 ->required()
    //                 ->reactive() // Hacer que el select sea reactivo
    //                 ->afterStateUpdated(function ($state){
    //                     // $this->dispatch('open-modal', id: 'loading-data');
    //                     // $this->updatedConditionId($state);
    //                     // $this->dispatch('close-modal', id: 'loading-data');
    //                 }),
    //         ]);
    // }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(function (Builder $query) {
                // $query = User::whereHas('dependentUser', function (Builder $query) {
                //     $query->whereHas('dependentConditions', function (Builder $query) {
                //         $query->where('condition_id', '=', $this->condition_id);
                //     });
                // });

                // $query = User::whereHas('dependentUser', function (Builder $query) {
                //     $query->with('dependentConditions');
                // });

                // $query = User::has('dependentUser');
                $query = User::query();
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('text')
                    ->label('Nombre Completo')
                    ->getStateUsing(function ($record) {
                        return ($record->text != null)?$record->text:$record->given . ' ' . $record->fathers_family . ' ' . $record->mothers_family;
                    }),
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
                Tables\Columns\TextColumn::make('dependentUser.created_at')
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
                Tables\Columns\TextColumn::make('dependentUser.diagnosis')
                    ->label('Diagnostico'),
                Tables\Columns\TextColumn::make('dependentUser.check_in_date')
                    ->label('Fecha de Ingreso')
                    ->date(),
                Tables\Columns\TextColumn::make('dependentUser.check_out_date')
                    ->label('Fecha de Egreso')
                    ->date(),
                Tables\Columns\TextColumn::make('dependentUser.integral_visits')
                    ->label('Vistas Integrales'),
                Tables\Columns\TextColumn::make('dependentUser.last_integral_visit')
                    ->label('Última Visita Integral')
                    ->date(),
                Tables\Columns\TextColumn::make('dependentUser.treatment_visits')
                    ->label('Visitas de Tratamiento'),
                Tables\Columns\TextColumn::make('dependentUser.last_treatment_visit')
                    ->label('Última Visita de Tratamiento')
                    ->date(),
                Tables\Columns\TextColumn::make('dependentUser.barthel')
                    ->label('Barthel'),
                Tables\Columns\TextColumn::make('dependentUser.empam')
                    ->label('Emp / Empam'),
                Tables\Columns\TextColumn::make('dependentUser.eleam')
                    ->label('Eleam')
                    ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                Tables\Columns\TextColumn::make('dependentUser.upp')
                    ->label('UPP')
                    ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                Tables\Columns\TextColumn::make('dependentUser.elaborated_plan')
                    ->label('Plan Elaborado')
                    ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                Tables\Columns\TextColumn::make('dependentUser.evaluated_plan')
                    ->label('Plan Evaluado')
                    ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                Tables\Columns\TextColumn::make('dependentUser.pneumonia')
                    ->label('Neumonia'),
                Tables\Columns\TextColumn::make('dependentUser.influenza')
                    ->label('Influenza'),
                Tables\Columns\TextColumn::make('dependentUser.covid_19')
                    ->label('Covid-19'),
                Tables\Columns\TextColumn::make('dependentUser.covid_19_date')
                    ->label('Fecha de Covid-19')
                    ->date(),
                Tables\Columns\TextColumn::make('dependentUser.extra_info')
                    ->label('Otros'),
                Tables\Columns\TextColumn::make('dependentUser.tech_aid')
                    ->label('Ayuda Técnica')
                    ->placeholder('No Aplica')
                    ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                Tables\Columns\TextColumn::make('dependentUser.tech_aid_date')
                    ->label('Fecha Ayuda Técnica')
                    ->date(),
                Tables\Columns\TextColumn::make('dependentUser.nutrition_assistance')
                    ->label('Entrega de Alimentación')
                    ->placeholder('No Aplica')
                    ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                Tables\Columns\TextColumn::make('dependentUser.nutrition_assistance_date')
                    ->label('Fecha Entrega de Alimentación')
                    ->date(),
                Tables\Columns\TextColumn::make('dependentUser.flood_zone')
                    ->label('Zona de Inundabilidad')
                    ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                // Agrega más columnas según tus necesidades
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Tipo de Condición')
                    ->placeholder('Seleccione')
                    ->options(Condition::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, $state){
                        $query->whereHas('dependentUser', function (Builder $query) use ($state) {
                            $query->whereHas('dependentConditions', function (Builder $query) use ($state) {
                                $query->where('condition_id', '=', $state);
                            });
                        });
                    }),
                Tables\Filters\Filter::make('user')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->statePath('name')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                        ->when(
                            $data['name'],
                            fn (Builder $query, $name): Builder => $query->where('text', 'like', '%' . $name . '%'),
                        );
                    })
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->headerActions([
                // Actions\Action::make('create')
                //     ->label('Crear')
                //     ->url(fn (): string => route('filament.admin.pages.dependent-user-create'))
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('')
                    ->icon('heroicon-c-pencil-square')
                    ->url(fn (User $record): string => route('filament.admin.pages.dependent-user-edit', ['user_id' => $record->id])),
                Tables\Actions\Action::make('map')
                    ->label('')
                    ->icon('heroicon-s-map')
                    ->url(fn (User $record): string => route('filament.admin.pages.dependent-user-map', ['condition_id' => $this->condition_id, 'user_id' => $record->id]))
            ])
            ->bulkActions([])
            // ->recordAction(Tables\Actions\ViewAction::class)
            ->recordUrl(null);
    }

    // public function updatedConditionId($conditionId)
    // {
    //     $this->condition_id = $conditionId;
    //     $this->table->query($this->getTableQuery());
    // }

    // protected function getTableQuery(): Builder
    // {
    //     // Aquí puedes personalizar la consulta según tus necesidades

    //     $usersWithConditions = User::whereHas('dependentUser', function (Builder $query) {
    //             $query->whereHas('dependentConditions', function (Builder $query) {
    //                 $query->where('condition_id', '=', $this->condition_id);
    //             });
    //         });
    //     return $usersWithConditions;
    // }

    // protected function getTableColumns(): array
    // {
    //     return [
    //         Tables\Columns\TextColumn::make('text')
    //             ->label('Nombre Completo'),
    //         Tables\Columns\TextColumn::make('sex')
    //             ->label('Sexo'),
    //         Tables\Columns\TextColumn::make('gender')
    //             ->label('Genero'),
    //         Tables\Columns\TextColumn::make('birthday')
    //             ->label('Fecha Nacimiento')
    //             ->date(),
    //         Tables\Columns\TextColumn::make('age')
    //             ->label('Edad')
    //             ->getStateUsing(function ($record) {
    //                 return Carbon::parse($record->birthday)->age;
    //             }),
    //         Tables\Columns\TextColumn::make('dependentUser.created_at')
    //             ->label('ingresado')
    //             ->date(),
    //         Tables\Columns\TextColumn::make('address.use')
    //             ->label('Tipo Dirección'),
    //         Tables\Columns\TextColumn::make('address.text')
    //             ->label('Calle'),
    //         Tables\Columns\TextColumn::make('address.line')
    //             ->label('N°'),
    //         Tables\Columns\TextColumn::make('address.commune.name')
    //             ->label('Comuna'),
    //         Tables\Columns\TextColumn::make('address.location.longitude')
    //             ->label('Longitud'),
    //         Tables\Columns\TextColumn::make('address.location.latitude')
    //             ->label('Latitud'),
    //         Tables\Columns\TextColumn::make('dependentUser.diagnosis')
    //             ->label('Diagnostico'),
    //         Tables\Columns\TextColumn::make('dependentUser.check_in_date')
    //             ->label('Fecha de Ingreso')
    //             ->date(),
    //         Tables\Columns\TextColumn::make('dependentUser.check_out_date')
    //             ->label('Fecha de Egreso')
    //             ->date(),
    //         Tables\Columns\TextColumn::make('dependentUser.integral_visits')
    //             ->label('Vistas Integrales'),
    //         Tables\Columns\TextColumn::make('dependentUser.last_integral_visit')
    //             ->label('Última Visita Integral')
    //             ->date(),
    //         Tables\Columns\TextColumn::make('dependentUser.treatment_visits')
    //             ->label('Visitas de Tratamiento'),
    //         Tables\Columns\TextColumn::make('dependentUser.last_treatment_visit')
    //             ->label('Última Visita de Tratamiento')
    //             ->date(),
    //         Tables\Columns\TextColumn::make('dependentUser.barthel')
    //             ->label('Barthel'),
    //         Tables\Columns\TextColumn::make('dependentUser.empam')
    //             ->label('Emp / Empam'),
    //         Tables\Columns\TextColumn::make('dependentUser.eleam')
    //             ->label('Eleam')
    //             ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
    //         Tables\Columns\TextColumn::make('dependentUser.upp')
    //             ->label('UPP')
    //             ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
    //         Tables\Columns\TextColumn::make('dependentUser.elaborated_plan')
    //             ->label('Plan Elaborado')
    //             ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
    //         Tables\Columns\TextColumn::make('dependentUser.evaluated_plan')
    //             ->label('Plan Evaluado')
    //             ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
    //         Tables\Columns\TextColumn::make('dependentUser.pneumonia')
    //             ->label('Neumonia'),
    //         Tables\Columns\TextColumn::make('dependentUser.influenza')
    //             ->label('Influenza'),
    //         Tables\Columns\TextColumn::make('dependentUser.covid_19')
    //             ->label('Covid-19'),
    //         Tables\Columns\TextColumn::make('dependentUser.covid_19_date')
    //             ->label('Fecha de Covid-19')
    //             ->date(),
    //         Tables\Columns\TextColumn::make('dependentUser.extra_info')
    //             ->label('Otros'),
    //         Tables\Columns\TextColumn::make('dependentUser.tech_aid')
    //             ->label('Ayuda Técnica')
    //             ->placeholder('No Aplica')
    //             ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
    //         Tables\Columns\TextColumn::make('dependentUser.tech_aid_date')
    //             ->label('Fecha Ayuda Técnica')
    //             ->date(),
    //         Tables\Columns\TextColumn::make('dependentUser.nutrition_assistance')
    //             ->label('Entrega de Alimentación')
    //             ->placeholder('No Aplica')
    //             ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
    //         Tables\Columns\TextColumn::make('dependentUser.nutrition_assistance_date')
    //             ->label('Fecha Entrega de Alimentación')
    //             ->date(),
    //         Tables\Columns\TextColumn::make('dependentUser.flood_zone')
    //             ->label('Zona de Inundabilidad')
    //             ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
    //         // Agrega más columnas según tus necesidades
    //     ];
    // }

    // protected function getTableActions(): array
    // {
    //     return [
    //         Tables\Actions\Action::make('edit')
    //             ->label('')
    //             ->icon('heroicon-c-pencil-square')
    //             ->url(fn (User $record): string => route('filament.admin.pages.dependent-user-edit', ['user_id' => $record->id])),
    //         Tables\Actions\Action::make('map')
    //             ->label('')
    //             ->icon('heroicon-s-map')
    //             ->url(fn (User $record): string => route('filament.admin.pages.dependent-user-map', ['condition_id' => $this->condition_id, 'user_id' => $record->id]))
    //     ];
    // }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Crear')
                ->url(fn (): string => route('filament.admin.pages.dependent-user-create')),
            FilamentExcel\Actions\Pages\ExportAction::make()->exports([
                FilamentExcel\Exports\ExcelExport::make('Descargar en Excel')->fromTable()->withColumns([
                    FilamentExcel\Columns\Column::make('text')
                        ->heading('Nombre Completo'),
                    FilamentExcel\Columns\Column::make('sex')
                        ->heading('Sexo'),
                    FilamentExcel\Columns\Column::make('gender')
                        ->heading('Genero'),
                    FilamentExcel\Columns\Column::make('birthday')
                        ->heading('Fecha Nacimiento'),
                    FilamentExcel\Columns\Column::make('age')
                        ->heading('Edad')
                        ->getStateUsing(function ($record) { 
                            return Carbon::parse($record->birthday)->age;
                        }),
                    FilamentExcel\Columns\Column::make('dependentUser.created_at')
                        ->heading('ingresado'),
                    FilamentExcel\Columns\Column::make('address.use')
                        ->heading('Tipo Dirección'),
                    FilamentExcel\Columns\Column::make('address.text')
                        ->heading('Calle'),
                    FilamentExcel\Columns\Column::make('address.line')
                        ->heading('N°'),
                    FilamentExcel\Columns\Column::make('address.commune.name')
                        ->heading('Comuna'),
                    FilamentExcel\Columns\Column::make('address.location.longitude')
                        ->heading('Longitud'),
                    FilamentExcel\Columns\Column::make('address.location.latitude')
                        ->heading('Latitud'),
                    FilamentExcel\Columns\Column::make('dependentUser.diagnosis')
                        ->heading('Diagnostico'),
                    FilamentExcel\Columns\Column::make('dependentUser.check_in_date')
                        ->heading('Fecha de Ingreso'),
                    FilamentExcel\Columns\Column::make('dependentUser.check_out_date')
                        ->heading('Fecha de Egreso'),
                    FilamentExcel\Columns\Column::make('dependentUser.integral_visits')
                        ->heading('Vistas Integrales'),
                    FilamentExcel\Columns\Column::make('dependentUser.last_integral_visit')
                        ->heading('Última Visita Integral'),
                    FilamentExcel\Columns\Column::make('dependentUser.treatment_visits')
                        ->heading('Visitas de Tratamiento'),
                    FilamentExcel\Columns\Column::make('dependentUser.last_treatment_visit')
                        ->heading('Última Visita de Tratamiento'),
                    FilamentExcel\Columns\Column::make('dependentUser.barthel')
                        ->heading('Barthel'),
                    FilamentExcel\Columns\Column::make('dependentUser.empam')
                        ->heading('Emp / Empam'),
                    FilamentExcel\Columns\Column::make('dependentUser.eleam')
                        ->heading('Eleam')
                        ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                    FilamentExcel\Columns\Column::make('dependentUser.upp')
                        ->heading('UPP')
                        ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                    FilamentExcel\Columns\Column::make('dependentUser.elaborated_plan')
                        ->heading('Plan Elaborado')
                        ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                    FilamentExcel\Columns\Column::make('dependentUser.evaluated_plan')
                        ->heading('Plan Evaluado')
                        ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                    FilamentExcel\Columns\Column::make('dependentUser.pneumonia')
                        ->heading('Neumonia'),
                    FilamentExcel\Columns\Column::make('dependentUser.influenza')
                        ->heading('Influenza'),
                    FilamentExcel\Columns\Column::make('dependentUser.covid_19')
                        ->heading('Covid-19'),
                    FilamentExcel\Columns\Column::make('dependentUser.covid_19_date')
                        ->heading('Fecha de Covid-19'),
                    FilamentExcel\Columns\Column::make('dependentUser.extra_info')
                        ->heading('Otros'),
                    FilamentExcel\Columns\Column::make('dependentUser.tech_aid')
                        ->heading('Ayuda Técnica')
                        ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                    FilamentExcel\Columns\Column::make('dependentUser.tech_aid_date')
                        ->heading('Fecha Ayuda Técnica'),
                    FilamentExcel\Columns\Column::make('dependentUser.nutrition_assistance')
                        ->heading('Entrega de Alimentación')
                        ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),
                    FilamentExcel\Columns\Column::make('dependentUser.nutrition_assistance_date')
                        ->heading('Fecha Entrega de Alimentación'),
                    FilamentExcel\Columns\Column::make('dependentUser.flood_zone')
                        ->heading('Zona de Inundabilidad')
                        ->formatStateUsing(fn($state)=>($state==1)?'Si':'No'),

                ])
            ])
        ];
    }
}
