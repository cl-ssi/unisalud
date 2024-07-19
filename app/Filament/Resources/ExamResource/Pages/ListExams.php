<?php

namespace App\Filament\Resources\ExamResource\Pages;

use Filament\Actions;
use App\Models\Exam;

use Livewire\Attributes\On;

use App\Filament\Resources\ExamResource;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    public array $filters = [];

    #[On('updateTableQuery')]
    public function updateTableQuery(array $search): void
    {
        // dd($search);
        $this->filters = $search;
        // dd($this->filters);
        $this->resetTable();
    }

    protected function modifyQueryWithActiveTab(Builder $query): Builder
    {

        $query = parent::modifyQueryWithActiveTab($query);
        /*
        $query = $query->select(
            'mx_exams.id',
            'mx_patients.run',
            'mx_patients.dv',
            'mx_patients.name',
            'mx_patients.fathers_family',
            'mx_patients.mothers_family',
            'mx_patients.gender',
            'mx_patients.telephone',
            'mx_patients.birthday',
            'mx_patients.address',
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
            'communes.name',
            'mx_establishments.alias'
        );
        */
        $query = $query->leftjoin('mx_patients', 'mx_exams.patient_id', 'mx_patients.id');
        $query = $query->leftjoin('communes', 'mx_exams.comuna', 'communes.code_deis');
        $query = $query->leftjoin('mx_establishments', 'mx_exams.cesfam', 'mx_establishments.new_code_deis');
        // ->where('mx_exams.establecimiento_realiza_examen', '=', $code_deis)
        // ->where('mx_exams.cesfam', '=', $code_deis_request)
        // ->where('mx_exams.comuna', '=', $commune)

        if (!empty($this->filters['run'])) {
            $query = $query->where('mx_patients.run', '=', $this->filters['run']);
        }

        else
        {
            $query->whereNull('mx_exams.id');
        }
        // dd($this->filters);
        // $this->filters = [];
        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExamResource\Widgets\SearchExamWidget::class,
        ];
    }
}
