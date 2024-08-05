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

    // protected static ?string $model = Exam::class;

    // protected static string $relationship = Exam::class;

    // we need to define the inverse relationship name otherwise it will look for plural
    // protected static ?string $inverseRelationship = Patient::class;

    public $birards = [];

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {


                $query = Patient::query()
                ->select('mx_patients.id, mx_exams.birards_mamografia, count(mx_exams.id) total')
                ->leftjoin('mx_exams', 'mx_patients.id', 'mx_exams.patient_id')
                ->whereNotNull('mx_exams.birards_mamografia')
                ->where('mx_exams.birards_mamografia', '<>' , "''")
                ->where('mx_exams.birards_mamografia', '>=' , 0)
                ->groupBy('mx_exams.birards_mamografia, mx_patients.id')

                // ->select(
                //     'mx_patients.id',
                //     'mx_patients.run',
                //     'mx_patients.dv',
                //     'mx_patients.name',
                //     'mx_patients.fathers_family',
                //     'mx_patients.mothers_family',
                //     'mx_patients.gender',
                //     'mx_patients.telephone',
                //     'mx_patients.birthday',
                //     'mx_patients.address',
                // )


                ;
                // dd($query->first());
                // $query->dd();
                return $query;
            })
            /*
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->leftjoin('mx_exams', 'mx_patients.id', 'mx_exams.patient_id')
                    ->where('mx_exams.birards_mamografia', '>=' , 0);
            }) */
            ->columns([
                // Tables\Columns\TextColumn::make('exams_count')->counts('exams')
                // Tables\Columns\TextColumn::make('id'),
                // Tables\Columns\TextColumn::make('fullname'),
                // Tables\Columns\TextColumn::make('exams.exam_type'),
                // Tables\Columns\TextColumn::make('exams.birards_mamografia'),

                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('birards_mamografia'),
                Tables\Columns\TextColumn::make('total'),

                // Tables\Columns\TextColumn::make('range1')
                /*
                Tables\Columns\TextColumn::make('exams_count')
                    ->counts([
                        'exams' => fn (Builder $query) => $query->where('patients->age', '<', 35)->where('mx_exams.birards_mamografia', '<>' , '""'),
                    ])
                */
                    // ->formatStateUsing(fn($state, Patient $patient)=>),
                    // ->label(function ( $row ) {})
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
