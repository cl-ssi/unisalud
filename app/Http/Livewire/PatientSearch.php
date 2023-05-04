<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Traits\GoogleToken;
use Illuminate\Support\Facades\Http;
use Livewire\WithPagination;

use Illuminate\Support\Facades\Auth;

class PatientSearch extends Component
{
    use GoogleToken;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $searchf = null;

    public function render()
    {
//        $url = $this->getUrlBase().'Patient'. ($this->searchf ? '?name:contains='.$this->searchf : '/');
//        $response = Http::withToken($this->getToken())->get($url);
//        // dd($this->getToken());
//        $query = $response->json();

//        if($query['total']>0) {
//            $patients = $query['entry'];
//        }
//        else $patients = array();

        $patients = User::query()
            ->whereHas('humanNames', function ($query) {
                $query->where('text', 'like', "%$this->searchf%")
                ->orwhere('fathers_family', 'like', "%$this->searchf%")
                ->orwhere('mothers_family', 'like', "%$this->searchf%");
            })
            //cuando sea usuario del programador, solo trae usuarios que sean del programador
            ->when(Auth::user()->hasPermissionTo('Mp: perfil administrador'), function ($query) {
                $query->permission('Mp: user');
            })
            ->paginate(200);

        return view('livewire.patient-search', ['patients' => $patients]);
    }
}
