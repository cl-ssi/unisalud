<?php

namespace App\Http\Livewire\Samu;

use Livewire\Component;
use App\Models\Samu\MobileType;

class MobileTypes extends Component
{
    public $types;
    public $view;

    public $type;
    public $name, $description, $valid_from, $valid_to, $status;

    protected function rules()
    {
        return [
            'name' => 'required|min:2',
            'description' => 'required',
            'valid_from' => 'required|date_format:Y-m-d',
            'valid_to' => 'required|date_format:Y-m-d',
            'status' => 'integer',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre es requerido.',
        'description.required' => 'La descripción es requerida.',
        'valid_from.required' => 'La vigencia desde es requerida.',
        'valid_to.required' => 'La vigencia hasta es requerida.',
        'status.required' => 'El estado es requerido.',
    ];

    public function mount()
    {
        $this->types = MobileType::orderBy('name')->get();
        $this->view = 'index';
    }

    public function index()
    {
        $this->view = 'index';
    }

    public function create()
    {
        $this->view = 'create';
        $this->type = null;
        
        $this->name = null;
        $this->description = null;
        $this->valid_from = null;
        $this->valid_to = null;
        $this->status = null;
    }

    public function store()
    {
        MobileType::create($this->validate());
        $this->mount();
        $this->view = 'index';
    }

    public function edit(MobileType $type)
    {
        $this->view = 'edit';
        $this->type = $type;
        
        $this->name = $type->name;
        $this->description = $type->description;
        $this->valid_from = $type->valid_from->format('Y-m-d');
        $this->valid_to = $type->valid_to->format('Y-m-d');
        $this->status = $type->status;
    }

    public function update(MobileType $type)
    {
        $type->update($this->validate());

        $this->mount();
        $this->view = 'index';
    }

    public function delete(MobileType $type)
    {
        $type->delete();
        $this->mount();
    }

    public function render()
    {
        return view('livewire.samu.mobile-types');
    }
}
