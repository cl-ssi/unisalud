<?php

namespace App\Livewire\Condition;

use App\Models\DependentUser;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\On;

class Map extends Component
{

    public $user_id;
    
    public $condition_id;

    public function render(): View
    {
        $this->condition_id = $this->condition_id??null;
        $this->user_id = $this->user_id??null;
        $dependentUsers = DependentUser::has('user.address.location')->with(['user.address', 'user.address.location'])
        ->when($this->condition_id, function ($query) {
            $query->whereHas('conditions', function ($query) {
                $query->where('condition_id', $this->condition_id);
            });
        })
        ->when($this->user_id, function ($query) {
            $query->whereHas('user', function ($query) {
                $query->where('id', $this->user_id);
            });
        })->get();
        $patients = $dependentUsers->map(function ($patient) {
            return [
                'id' => $patient->id,
                'name' => $patient->user->text,
                'address' => $patient->user->address->text . ' ' . $patient->user->address->line,
                'latitude' => $patient->user->address->location->latitude,
                'longitude' => $patient->user->address->location->longitude,
            ];
        });
        return view('livewire.condition.map', ['patients' => $patients]);
       
        // return view('livewire.condition.map', ['patients' => []]);
        
    }

    #[On('changeFilters')]
    public function changeFilters($condition_id = null, $user_id = null)
    {
        $this->condition_id = $condition_id??$this->condition_id;
        $this->user_id = $user_id??$this->user_id;
    }
}
