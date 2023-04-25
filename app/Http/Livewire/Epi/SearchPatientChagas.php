<?php

namespace App\Http\Livewire\Epi;

use Livewire\Component;
use App\Models\Epi\SuspectCase;
use App\Models\User;

class SearchPatientChagas extends Component
{

    public $search;
    public $suspectcases;
    public $message;    

    protected $patients;

    public function searchPatient()
    {
        $patients = User::getUsersByIdentifier($this->search);

        if ($patients->count() === 0) {
            $this->message = "No se encontró ningún paciente con ese número de identificación o RUT";
            $this->suspectcases = null;
            $this->patients = null;
        } else {
            $patientIds = $patients->pluck('id')->toArray();
            $this->suspectcases = SuspectCase::whereIn('patient_id', $patientIds)
                ->where('chagas_result_confirmation', 'Positivo')
                ->get();
            if ($this->suspectcases->count() === 0) {
                $this->message = "No se encontró ningún paciente madre chagásica";
            } else {
                $this->message = "Se encontró al menos un paciente madre chagásica";
            }
            $this->patients = $patients;
        }
    }

    


    public function render()
    {
        return view('livewire.epi.search-patient-chagas');
    }
}
