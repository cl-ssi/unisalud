<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\Patient;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListBirards extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;


    public $name = "";
    public $attr = "";
    public $tittle = "";
    public $type;
    public $filters;

    public function mount(): void
    {
        if($this->type == 'mam')
        {
            $this->name = 'mx_exams.birards_mamografia';
            $this->attr = 'birards_mamografia';
            $this->tittle = 'BIRARDS POR RANGO DE EDAD MAMOGRAFÍA';
        }
        else if ($this->type == 'eco')
        {
            $this->name = 'mx_exams.birards_ecografia';
            $this->attr = 'birards_ecografia';
            $this->tittle = 'BIRARDS POR RANGO DE EDAD ECOGRAFÍA';
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $query = Patient::query()
                ->leftjoin('mx_exams', 'mx_patients.id', 'mx_exams.patient_id')
                ->select(
                    'mx_patients.id',
                    $this->name,
                    DB::raw('SUM(case when YEAR(CURDATE())-YEAR(birthday) < 35 then 1 else 0 end) range1'),
                    DB::raw('SUM(case when YEAR(CURDATE())-YEAR(mx_patients.birthday) >= 35 and YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 49 then 1 else 0 end) range2'),
                    DB::raw('SUM(case when YEAR(CURDATE())-YEAR(mx_patients.birthday) > 49 and YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 54 then 1 else 0 end) range3'),
                    DB::raw('SUM(case when YEAR(CURDATE())-YEAR(mx_patients.birthday) > 54 and YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 59 then 1 else 0 end) range4'),
                    DB::raw('SUM(case when YEAR(CURDATE())-YEAR(mx_patients.birthday) > 59 and YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 64 then 1 else 0 end) range5'),
                    DB::raw('SUM(case when YEAR(CURDATE())-YEAR(mx_patients.birthday) > 64 and YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 69 then 1 else 0 end) range6'),
                    DB::raw('SUM(case when YEAR(CURDATE())-YEAR(mx_patients.birthday) > 69 and YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 74 then 1 else 0 end) range7'),
                    DB::raw('SUM(case when YEAR(CURDATE())-YEAR(mx_patients.birthday) > 74 and YEAR(CURDATE())-YEAR(mx_patients.birthday) <= 79 then 1 else 0 end) range8'),
                    DB::raw('SUM(case when YEAR(CURDATE())-YEAR(mx_patients.birthday) > 79  then 1 else 0 end) range9'),
                    DB::raw('COUNT(YEAR(CURDATE())-YEAR(mx_patients.birthday)) total'),
                    )
                ->where($this->name, '<>' , "")
                ->whereIn($this->name, ['0', '1', '2', '3', '4', '5', '6', '7'])
                ->groupBy(
                    $this->name,
                    'mx_patients.id'
                );
                return $query;
            })
            ->modifyQueryUsing(function (Builder $query) {
                if($this->filters)
                {
                }
                else
                {
                    $query->whereNull('mx_exams.id');
                }
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make($this->attr)
                    ->label('BIRARDS'),
                Tables\Columns\TextColumn::make('range1')
                    ->label('< 35')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                    ->label('')
                ),
                Tables\Columns\TextColumn::make('range2')
                    ->label('35 a 49')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                    ->label('')),
                Tables\Columns\TextColumn::make('range3')
                    ->label('50 a 54')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
                Tables\Columns\TextColumn::make('range4')
                    ->label('55 a 59')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
                Tables\Columns\TextColumn::make('range5')
                    ->label('60 a 64')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
                Tables\Columns\TextColumn::make('range6')
                    ->label('65 a 69')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
                Tables\Columns\TextColumn::make('range7')
                    ->label('70 a 74')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
                Tables\Columns\TextColumn::make('range8')
                    ->label('75 a 79')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
                Tables\Columns\TextColumn::make('range9')
                    ->label('80 y más')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
                Tables\Columns\TextColumn::make('total')
                    ->label('total')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('')
                    ),
            ])
            ->groups([
                Tables\Grouping\Group::make($this->attr)
                    ->collapsible(),
            ])
            ->defaultGroup($this->attr)
            ->groupsOnly()
            ->groupingSettingsHidden()
            ->heading($this->tittle)
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render(): View
    {
        return view('livewire.list-birards');
    }
}
