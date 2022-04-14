<?php

namespace App\Http\Livewire\Samu;

use Livewire\Component;
use App\Models\Samu\Supply;

class Supplies extends Component
{
    public $supplies;
    public $view;

    public $supply;
    public $category_id, $code, $name, $valid_from, $valid_to, $value;

    protected $rules = [
        'category_id' => 'required',
        'code' => 'required',
        'name' => 'required|min:4',
        'valid_from' => 'required|date_format:Y-m-d',
        'valid_to' => 'nullable|date',
        'value' => 'integer',
    ];

    protected $messages = [
        'category_id.required' => 'La categoría es obligatoria.',
        'code.required' => 'El código es requerido.',
        'name.required' => 'El nombre es requerido.',
        'valid_from.required' => 'La vigencia desde es requerida.',
        'value.required' => 'El valor del procedimiento es requerido.',
    ];

    public function mount()
    {
        $this->supplies = Supply::orderBy('name')->get();
        $this->view = 'index';
    }

    public function index()
    {
        $this->view = 'index';
    }

    public function create()
    {
        $this->view = 'create';
        $this->supply = null;
        
        $this->category_id = null;
        $this->code = null;
        $this->name = null;
        $this->valid_from = null;
        $this->valid_to = null;
        $this->value = null;
    }

    public function store()
    {
        Supply::create($this->validate());
        $this->mount();
        $this->view = 'index';
    }

    public function edit(Supply $supply)
    {
        $this->view = 'edit';
        $this->supply = $supply;
        
        $this->category_id = $supply->category_id;
        $this->code = $supply->code;
        $this->name = $supply->name;
        $this->valid_from = $supply->valid_from->format('Y-m-d');
        $this->valid_to = optional($supply->valid_to)->format('Y-m-d');
        $this->value = $supply->value;
    }

    public function update(Supply $supply)
    {
        $supply->update($this->validate());

        $this->mount();
        $this->view = 'index';
    }

    public function delete(Supply $supply)
    {
        $supply->delete();
        $this->mount();
    }

    public function render()
    {
        return view('livewire.samu.supplies');
    }
}
