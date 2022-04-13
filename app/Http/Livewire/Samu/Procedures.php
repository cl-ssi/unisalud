<?php

namespace App\Http\Livewire\Samu;

use Livewire\Component;
use App\Models\Samu\Procedure;

class Procedures extends Component
{
    public $procedures;
    public $view;

    public $procedure;
    public $code,$name,$valid_from,$valid_to;

    public function mount()
    {
        $this->procedures = Procedure::orderBy('name')->get();
        $this->view = 'index';
    }

    public function index()
    {
        $this->view = 'index';
    }

    public function edit(Procedure $procedure)
    {
        $this->view = 'edit';
        $this->procedure = $procedure;
        
        $this->code = $procedure->code;
        $this->name = $procedure->name;
        $this->valid_from = $procedure->valid_from->format('Y-m-d');
        $this->valid_to = $procedure->valid_to;
    }

    public function update(Procedure $procedure)
    {
        $this->procedure->code = $this->code;
        $this->procedure->name = $this->name;
        $this->procedure->valid_from = $this->valid_from;
        $this->procedure->valid_to = $this->valid_to;
        $this->procedure->save();

        $this->mount();
        $this->view = 'index';
    }

    public function delete(Procedure $procedure)
    {
        $procedure->delete();
        $this->mount();
    }

    public function render()
    {
        return view('livewire.samu.procedures');
    }
}
