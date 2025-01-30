<?php

namespace App\Filament\Pages\SireMx;

use App\Models\Exam;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class ReportBirardsAge extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.sire-mx.report-birards-age';

    protected static ?string $navigationGroup = 'Exámenes Mamarios';

    protected static ?string $navigationLabel = 'Reporte REM';

    protected static ?string $title = 'Reporte REM';

    protected static ?int $navigationSort = 7;

    protected $listeners = ['updateFilters' => 'setFilters'];

    public $filters;

    public static function canAccess(): bool
    {   
        if(auth()->user()->can('be god')){
            return true;
        }
        return auth()->user()->can('SireMx: Manager');
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
        $this->dispatch('form-submited', $this->filters);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $query = Exam::query();
                $query->leftjoin('mx_patients', 'mx_exams.patient_id', 'mx_patients.id');
                $query->leftjoin('communes', 'mx_exams.comuna', 'communes.code_deis');
                $query->leftjoin('mx_establishments', 'mx_exams.cesfam', 'mx_establishments.new_code_deis');
                $query->select(
                    'mx_exams.id',
                    'mx_exams.patient_id',
                    'mx_exams.cesfam',
                    'mx_exams.date_exam_order',
                    'mx_exams.date_exam',
                    'mx_exams.date_exam_reception',
                    'mx_exams.birards_mamografia',
                    'mx_exams.birards_ecografia',
                    'mx_exams.birards_proyeccion',
                    'mx_exams.diagnostico',
                    'mx_exams.profesional_solicita',
                    'mx_exams.medico',
                    'mx_exams.servicio_salud',
                    'mx_exams.comuna',
                    'mx_patients.id',
                    'mx_patients.run',
                    'mx_patients.dv',
                    'mx_patients.name',
                    'mx_patients.fathers_family',
                    'mx_patients.mothers_family',
                    'mx_patients.gender',
                    'mx_patients.telephone',
                    'mx_patients.birthday',
                    'mx_patients.address',
                    'communes.name',
                    'communes.code_deis',
                    'mx_establishments.new_code_deis',
                    'mx_establishments.alias'
                );
                return $query;
            });
    }
}
