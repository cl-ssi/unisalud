<?php

namespace App\Http\Livewire\Samu;

use Livewire\Component;
use App\Models\Samu\Procedure;

class Procedures extends Component
{
    public $procedures;
    public $view;

    public $procedure;
    public $code,$name,$valid_from,$valid_to,$value;

    protected $rules = [
        'code' => 'required',
        'name' => 'required|min:4',
        'valid_from' => 'required|date_format:Y-m-d',
        'valid_to' => 'nullable|date',
        'value' => 'integer',
    ];

    protected $messages = [
        'code.required' => 'El código es requerido.',
        'name.required' => 'El nombre es requerido.',
        'valid_from.required' => 'La vigencia desde es requerida.',
        'value.required' => 'El valor del procedimiento es requerido.',
    ];

    public function mount()
    {
        $this->procedures = Procedure::orderBy('name')->get();
        $this->view = 'index';
    }

    public function index()
    {
        $this->view = 'index';
    }

    public function create()
    {
        $this->view = 'create';
        $this->procedure = null;
        
        $this->code = null;
        $this->name = null;
        $this->valid_from = null;
        $this->valid_to = null;
        $this->value = null;
    }

    public function store()
    {
        Procedure::create($this->validate());
        $this->mount();
        $this->view = 'index';
    }

    public function edit(Procedure $procedure)
    {
        $this->view = 'edit';
        $this->procedure = $procedure;
        
        $this->code = $procedure->code;
        $this->name = $procedure->name;
        $this->valid_from = $procedure->valid_from->format('Y-m-d');
        $this->valid_to = optional($procedure->valid_to)->format('Y-m-d');
        $this->value = $procedure->value;
    }

    public function update(Procedure $procedure)
    {
        $procedure->update($this->validate());

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
