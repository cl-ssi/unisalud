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

    public $birards = [];

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $query = Patient::query()
                ->leftjoin('mx_exams', 'mx_patients.id', 'mx_exams.patient_id')
                ->select(
                    'mx_patients.id',
                    'mx_exams.birards_mamografia',
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
                // ->whereNotNull('mx_exams.birards_mamografia')
                ->where('mx_exams.birards_mamografia', '<>' , "")
                ->where('mx_exams.birards_mamografia', '>=' , 0)
                ->groupBy(
                    'mx_exams.birards_mamografia',
                    'mx_patients.id'
                );
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('birards_mamografia'),
                Tables\Columns\TextColumn::make('range1')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
                Tables\Columns\TextColumn::make('range2')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
                Tables\Columns\TextColumn::make('range3')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
                Tables\Columns\TextColumn::make('range4')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
                Tables\Columns\TextColumn::make('range5')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
                Tables\Columns\TextColumn::make('range6')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
                Tables\Columns\TextColumn::make('range7')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
                Tables\Columns\TextColumn::make('range8')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
                Tables\Columns\TextColumn::make('range9')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
                Tables\Columns\TextColumn::make('total')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('')),
            ])
            ->groups([
                Tables\Grouping\Group::make('birards_mamografia')
                    ->collapsible(),
            ])
            ->defaultGroup('birards_mamografia')
            ->groupsOnly()
            ->groupingSettingsHidden()
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
