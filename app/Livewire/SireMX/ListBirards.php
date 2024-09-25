<?php

namespace App\Livewire\SireMX;

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
use Livewire\Attributes\On;
use Livewire\Component;

use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel;

class ListBirards extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected $listeners = ['updateTable' => '$refresh'];

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
        // dd($this->filters);
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
                    if(!empty($this->filters['inicio'])){
                        $query->where('mx_exams.date_exam', '>=', $this->filters['inicio']);
                    }
                    if(!empty($this->filters['final'])){
                        $query->where('mx_exams.date_exam', '<=', $this->filters['final']);
                    }
                    if (!empty($this->filters['commune'])) {
                        $query->where('mx_exams.comuna', '=', $this->filters['commune']);
                    }
                    if (!empty($this->filters['code_deis'])) {
                        //TODO: Auth::user()->establishment_id
                        $query->where('mx_exams.establecimiento_realiza_examen', '=', $this->filters['code_deis']);
                    }
                    if (!empty($this->filters['code_deis_request'])) {
                        //TODO: Auth::user()->establishment_id
                        $query->where('mx_exams.cesfam', '=', $this->filters['code_deis_request']);
                    }
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
                    ->label('Total')
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

    #[On('form-submited')]
    public function test($filters){
        $this->filters = $filters;
        // dd($this->filters);
    }

    protected function getHeaderActions(): array
    {
        date_default_timezone_set('America/Santiago');
        return [

            ExportAction::make()->exports([

                // Excel Export with custom format
                ExcelExport::make('Descargar en Excel')->fromTable()->withColumns([
                    FilamentExcel\Columns\Column::make($this->attr)
                        ->heading('BIRARDS'),
                    FilamentExcel\Columns\Column::make('range1')
                        ->heading('< 35'),
                    FilamentExcel\Columns\Column::make('range2')
                        ->heading('35 a 49'),
                    FilamentExcel\Columns\Column::make('range3')
                        ->heading('50 a 54'),
                    FilamentExcel\Columns\Column::make('range4')
                        ->heading('55 a 59'),
                    FilamentExcel\Columns\Column::make('range5')
                        ->heading('60 a 64'),
                    FilamentExcel\Columns\Column::make('range6')
                        ->heading('65 a 69'),
                    FilamentExcel\Columns\Column::make('range7')
                        ->heading('70 a 74'),
                    FilamentExcel\Columns\Column::make('range8')
                        ->heading('75 as 79'),
                    FilamentExcel\Columns\Column::make('range9')
                        ->heading('80 y mas'),
                    FilamentExcel\Columns\Column::make('total')
                        ->heading('Total'),
                ])
                ->withFilename('Reporte_' . $this->attr . '-' . date('dmY_Hs'))
            ])
        ];
    }


    public function render(): View
    {
        return view('livewire.sire-mx.list-birards');
    }
}
