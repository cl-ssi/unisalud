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

                /*

                ->orderBy('mx_exams.birards_mamografia')
                ->groupBy('mx_exams.birards_mamografia', 'mx_patients.birthday', 'mx_patients.id');
                 */
                // $query = Patient::query()
                $query = Exam::query()
                // ->leftjoin('mx_exams', 'mx_patients.id', 'mx_exams.patient_id')
                ->leftjoin('mx_patients', 'mx_exams.patient_id', 'mx_patients.id')
                ->select(
                    'mx_patients.id',
                    'mx_exams.birards_mamografia',
                    'mx_patients.birthday'
                )
                ->orderBy('mx_exams.birards_mamografia')
                ->groupBy('mx_exams.birards_mamografia', 'mx_patients.birthday');
                // $query->with(["patients"]);




                dd($query->first());
                return $query;
            })
            ->columns([
                // Tables\Columns\TextColumn::make('patients.age')
                //     ->numeric(decimalPlaces: 0)
                //     ->summarize([
                //         Tables\Columns\Summarizers\Count::make()->query(fn (Builder $query) => $query->where('patients.age','<',35)),
                //     ]),
                Tables\Columns\TextColumn::make('mx_exams.birards_mamografia')
                    // ->formatStateUsing(fn($state, Exam $exam)=>$exam->patients->age),
                    // ->query(fn (Builder $query) => $query->select(DB::raw('count(*) as range1'))->where('patients.age','<',35))

            ])
            ->groups([
                // Tables\Grouping\Group::make('birards_mamografia')

            ])
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
