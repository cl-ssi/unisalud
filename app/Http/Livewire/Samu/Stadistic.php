<?php

namespace App\Http\Livewire\Samu;

use Livewire\Component;
use App\Models\Samu\Event;
use App\Models\Samu\Key;
use App\Models\Commune;

class Stadistic extends Component
{
    public $keys;
    public $communes;

    public $key_id;
    public $kreturn_key_id;
    public $commune_id;
    public $from;
    public $to;

    public $total = null;

    public $chartData = array();
    public $chartDataInJson;

    public function mount()
    {
        $this->keys      = Key::orderBy('key')->get();
        $this->communes  = Commune::whereHas('samu')->pluck('id','name')->sort();
        $this->chartData[] = array("Comuna","Eventos");
    }

    public function search()
    {
        foreach($this->communes as $commune => $key)
        {
            $this->chartData[] = array($commune, Event::where('commune_id',$key)->count());
        }
        $this->total = Event::where('key_id',$this->key_id)->count();

        $this->emit('re-render',$this->chartData);
    }

    public function render()
    {
        $this->chartDataInJson = json_encode($this->chartData);
        return view('livewire.samu.stadistic');
    }
}
