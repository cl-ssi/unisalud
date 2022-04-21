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
    public $return_key_id;
    public $commune_id;
    public $from;
    public $to;

    public $total = null;

    protected $rules =  [
        'from' => 'required|date',
        'to'   => 'required|date'
    ];

    protected $messages = [
        'from.required' => 'La fecha desde es obligatoria.',
        'to.required' => 'La fecha hasta es obligatoria.',
    ];

    public function mount()
    {
        $this->from      = now()->firstOfMonth()->format('Y-m-d');
        $this->to        = now()->lastOfMonth()->format('Y-m-d');
        $this->keys      = Key::orderBy('key')->get();
        $this->communes  = Commune::whereHas('samu')->orderBy('name')->pluck('name','id');
    }

    public function search()
    {
        $this->validate();
        $chartData = array();
        $this->total = 0;
        
        foreach($this->communes as $id => $name)
        {
            $totalEvents = Event::query();

            if($this->key_id)
            {
                $totalEvents->where('key_id',$this->key_id);
            }

            $totalEvents->whereBetween('date',[$this->from,$this->to]);
            $this->total = $totalEvents->count();
            
            $totalEvents->where('commune_id',$id);    

            if($totalEvents->count() > 0)
            {
                $chartData[] = array($name,$totalEvents->count());
            }
        }

        if($this->key_id)
        {
            $totalEvents = Event::whereNull('commune_id')
                ->where('key_id',$this->key_id)
                ->whereBetween('date',[$this->from,$this->to])
                ->count();
        }
        else
        {
            $totalEvents = Event::whereNull('commune_id')
                ->whereBetween('date',[$this->from,$this->to])
                ->count();
        }

        $this->total += $totalEvents;

        if($totalEvents > 0)
        {
            $chartData[] = array('Sin comuna',$totalEvents);
        }

        array_unshift($chartData, ["Comuna", "Eventos"]);

        $this->emit('re-render', $chartData);
    }

    public function render()
    {
        // $this->chartDataInJson = json_encode($this->chartData);
        return view('livewire.samu.stadistic');
    }
}
