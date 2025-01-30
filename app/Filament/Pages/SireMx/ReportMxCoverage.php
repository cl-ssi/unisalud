<?php

namespace App\Filament\Pages\SireMx;

use App\Models\Exam;
use App\Models\Patient; // TODO: Replace with App\Models\User once we have SireMx Exam Form;
use App\Models\Commune;
use App\Models\Establishment;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Tables\Filters;
use Filament\Tables\Filters\QueryBuilder\Constraints;

use Filament\Forms;
use Filament\Forms\Form;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class ReportMxCoverage extends Page implements Forms\Contracts\HasForms, Tables\Contracts\HasTable
{

    use Forms\Concerns\InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;
    // use Tables\Concerns\HasFilters;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.sire-mx.report-mx-coverage';
    
    protected static ?string $navigationGroup = 'Exámenes Mamarios';

    protected static ?string $navigationLabel = 'Cobertura MX';

    protected static ?string $title = 'Cobertura MX';

    protected static ?int $navigationSort = 6;

    protected static ?bool $hasPageSummary = false;

    public $filters;

    public static function canAccess(): bool
    {   
        if(auth()->user()->can('be god')){
            return true;
        }
        return auth()->user()->can('SireMx: Manager');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                // TODO: Replace Patient with App\Models\User once we have SireMx Exam Form;
                $query = Patient::select(
                    'mx_patients.id',
                    DB::raw("(case when (YEAR(CURDATE())-YEAR(mx_patients.birthday) < 15) then '0 > 15'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 15 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 19) then '15 > 19'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 20 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 24) then '20 > 24'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 25 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 29) then '25 > 29'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 30 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 34) then '30 > 34'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 35 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 39) then '35 > 39'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 40 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 44) then '40 > 44'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 45 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 49) then '45 > 49'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 50 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 54) then '50 > 54'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 55 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 59) then '55 > 59'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 60 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 64) then '60 > 64'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 65 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 69) then '65 > 69'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 70 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 74) then '70 > 74'
                        when (YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 75 AND YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 79) then '75 > 79'
                        else '80 y Más' end) AS age_range
                    "),
                    DB::raw("SUM(case when mx_exams.birards_mamografia > 0 then 1 else 0 end)  AS mam "),
                    DB::raw("SUM(case when mx_exams.birards_ecografia  > 0 then 1 else 0 end)  AS eco"),
                    DB::raw("SUM(case when mx_exams.birards_proyeccion > 0 then 1 else 0 end)  AS pro")                    
                )
                ->leftJoin('mx_exams', 'mx_patients.id', '=', 'mx_exams.patient_id')
                ->groupBy("mx_patients.id")
                ->with('exams', 'exams.commune');
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('age_range'),
                Tables\Columns\TextColumn::make('mam')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
                Tables\Columns\TextColumn::make('eco')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
                Tables\Columns\TextColumn::make('pro')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
            ])
            ->groups([
                Tables\Grouping\Group::make('age_range')
                    ->collapsible()
                    ->label('Edad'),
            ])
            ->defaultGroup('age_range')
            ->groupsOnly()
            ->groupingSettingsHidden()
            ->filters([                
                Tables\Filters\Filter::make('mx_exams.date_exam')
                    ->form([
                        Forms\Components\DatePicker::make('since')
                            ->label('Inicio')
                            ->statePath('since'),
                        Forms\Components\DatePicker::make('to')
                            ->label('Fin')
                            ->statePath('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                        ->when(
                            $data['since'] && $data['to'],
                            fn (Builder $query): Builder => $query->where('mx_exams.date_exam', '>=', $data['since'])->where('mx_exams.date_exam', '<=', $data['to']),
                            fn (Builder $query): Builder => $query->whereNull('mx_patients.id')
                        );
                    })
                    ->columnSpan(2)
                    ->columns(2),
                Tables\Filters\SelectFilter::make('Comuna')                    
                    ->placeholder('Seleccione')
                    // ->relationship('exams.commune', 'name', fn (Builder $query) => $query->Wherein('region_id',['1']) )
                    ->options(
                        Commune::Where('name','LIKE','%%')
                        ->Wherein('region_id',['1'])
                        ->get()
                        ->pluck('name', 'code_deis')
                    )
                    ->query(function (Builder $query, $state): Builder {         
                        return $query
                        ->when(
                            $state['value'],
                            fn (Builder $query): Builder => $query->where('mx_exams.comuna', '=', $state['value'])
                        );
                    }),
                Tables\Filters\SelectFilter::make('Establecimiento Origen')
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
                    )
                    ->query(function (Builder $query, $state): Builder {
                        return $query
                        ->when(
                            $state['value'],
                            fn (Builder $query, $cesfam): Builder => $query->where('mx_exams.cesfam', '=', $state['value'])
                        );
                    }),
                    
                Tables\Filters\SelectFilter::make('mx_exams.establecimiento_realiza_examen')
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
                    )
                    ->query(function (Builder $query, $state): Builder {
                        return $query
                        ->when(
                            $state['value'],
                            fn (Builder $query, $exam_est): Builder => $query->where('mx_exams.establecimiento_realiza_examen', '=', $state['value'])
                        );
                    }),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(2);
    }
}
