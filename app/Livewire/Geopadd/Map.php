<?php

namespace App\Livewire\Geopadd;

use Livewire\Component;

class Map extends Component
{
    public $conditions_id = null;
    public $organizations_id = null;
    public $users_id = null;
    public $risks = null;

    protected $listeners = ['changeFilters'];


    public function mount()
    {
        // dd(asset('/js/geopadd/map.js'));
        // $this->baseUrl = env('APP_URL', 'https://uni.saludtarapaca.gob.cl/');
    }

    public function render()
    {
        return view('livewire.geopadd.map');
        // return view('livewire.geopadd.map', ['data' => [
        //     'conditions_id' => $this->conditions_id,
        //     'organizations_id' => $this->organizations_id,
        //     'users_id' => $this->users_id,
        //     'risks' => $this->risks
        // ]]);
    }

    public function changeFilters($conditions_id, $organizations_id, $req_users_id, $risks)
    {
        $this->conditions_id = $conditions_id;
        $this->organizations_id = $organizations_id;
        $this->users_id = $req_users_id;
        $this->risks = $risks;
        $this->dispatch('dataUpdated');
        // $this->dispatch('dataUpdated', ['data' => [
        //     'conditions_id' => $this->conditions_id,
        //     'organizations_id' => $this->organizations_id,
        //     'users_id' => $this->users_id,
        //     'risks' => $this->risks
        // ]]);
    }
}
