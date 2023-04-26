<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientAdvancedSearch extends Component
{

    public $searchByHumanName = null;
    public $searchByIdentifier = null;
    public $searchByAddress = null;
    public $searchByContactPoint = null;
    public $patients = null;
    public $original_id = null;

    public function search()
    {
        if($this->searchByHumanName || $this->searchByIdentifier || $this->searchByAddress || $this->searchByContactPoint){
            $this->patients = User::query()
                ->when($this->searchByHumanName, function ($query) {
                    $query->whereHas('humanNames', function ($query) {
                        $columns = [DB::raw("concat(SUBSTRING_INDEX(text, ' ', 1), ' ', fathers_family)"), 'text', 'fathers_family', 'mothers_family'];
                        $query->where(function ($q) use ($columns) {
                            foreach ($columns as $column)
                                $q->orWhere($column, 'like', "%{$this->searchByHumanName}%");
                        });
                    });
                })
                ->when($this->searchByIdentifier, function ($query) {
                    $query->whereHas('identifiers', function ($query) {
                        $query->where('value', $this->searchByIdentifier);
                    });
                })
                ->when($this->searchByAddress, function ($query) {
                    $query->whereHas('addresses', function ($query) {
                        $columns = [DB::raw("concat(text, ' ', line)"), 'text', 'line', 'apartment'];
                        $query->where(function ($q) use ($columns) {
                            foreach ($columns as $column)
                                $q->orWhere($column, 'like', "%{$this->searchByAddress}%");
                        });
                    });
                })
                ->when($this->searchByContactPoint, function ($query) {
                    $query->whereHas('contactPoints', function ($query) {
                        $query->where('value', 'like', "%{$this->searchByContactPoint}%");
                    });
                })
                // //cuando sea usuario del programador, solo trae usuarios que sean del programador
                // ->when(Auth::user()->hasPermissionTo('Mp: user creator'), function ($query) {
                //     $query->permission('Mp: user');
                // })
                ->get();
        }
    }

    public function clean()
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.patient-advanced-search');
    }
}
