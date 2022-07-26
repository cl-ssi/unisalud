<?php

namespace App\Http\Livewire\Aps;

use App\Models\User;
use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Aps\MinorAuthorization;

class MinorAuthorizationSearch extends Component
{
    public $minorAuthorizations = null;

    public $searchByName = null;
    public $searchByRun = null;

    public function search()
    {
        if($this->searchByName || $this->searchByRun){
            $this->minorAuthorizations = MinorAuthorization::when($this->searchByName, function ($query) {
                                                            $query->whereHas('authorizer', function ($query) {
                                                                $query->whereHas('humanNames', function ($query) {
                                                                    $columns = [DB::raw("concat(SUBSTRING_INDEX(text, ' ', 1), ' ', fathers_family)"), 'text', 'fathers_family', 'mothers_family'];
                                                                    $query->where(function ($q) use ($columns) {
                                                                        foreach ($columns as $column)
                                                                            $q->orWhere($column, 'like', "%{$this->searchByName}%");
                                                                    });
                                                                });
                                                            });
                                                        })
                                                        ->when($this->searchByRun, function ($query) {
                                                            $query->whereHas('authorizer', function ($query) {
                                                                $query->whereHas('identifiers', function ($query) {
                                                                    $query->where('value', $this->searchByRun);
                                                                });
                                                            });
                                                        })
                                                        ->get();
        }
    }

    public function clean()
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.aps.minor-authorization-search');
    }
}
