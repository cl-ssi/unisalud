<?php

namespace App\Livewire\DependentUser;

use Livewire\Component;
use App\Filament\Imports\ConditionImporter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;

class CreateDependentUser extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public $hasCaregiver = false;


    public function render(): View
    {
        return view('livewire.dependent-user.create-dependent-user');
    }


    public function save()
    {
        $data = $this->validate([
            'data.nombre'                       => 'required|string',
            'data.apellido_paterno'             => 'required|string',
            'data.apellido_materno'             => 'required|string',
            'data.run'                          => 'required|numeric',
            'data.dv'                           => 'required|numeric',
            'data.fecha_nacimiento'             => 'required|date',
            'data.sexo'                         => 'required|string',
            'data.genero'                       => 'nullable|string',
            'data.nacionalidad'                 => 'required|string',
            'data.comuna'                       => 'required|string',
            'data.calle'                        => 'required|string',
            'data.numero'                       => 'required|string',
            'data.departamento'                 => 'nullable|string',
            'data.telefono'                     => 'required|numeric',
            'data.prevision'                    => 'required|string',
            'data.diagnostico'                  => 'required|string',
            'data.fecha_ingreso'                => 'nullable|date',
            'data.fecha_egreso'                 => 'nullable|date',
            'data.establecimiento'              => 'nullable|string',
            'data.visitas_integrales'           => 'nullable|numeric',
            'data.visitas_tratamiento'          => 'nullable|numeric',
            'data.emp_empam'                    => 'nullable|boolean',
            'data.eleam'                        => 'nullable|boolean',
            'data.upp'                          => 'nullable|boolean',
            'data.plan_elaborado'               => 'nullable|boolean',
            'data.plan_evaluado'                => 'nullable|boolean',
            'data.neumo'                        => 'nullable|date',
            'data.influenza'                    => 'nullable|date',
            'data.covid_19'                     => 'nullable|date',
            'data.extra_info'                   => 'nullable|string',
            'data.ayuda_tecnica'                => 'nullable|boolean',
            'data.ayuda_tecnica_fecha'          => 'nullable|date',
            'data.entrega_alimentacion'         => 'nullable|boolean',
            'data.entrega_alimentacion_fecha'   => 'nullable|date',
            'data.sonda_sng'                    => 'nullable|numeric',
            'data.sonda_urinaria'               => 'nullable|numeric',
            'data.prevision_cuidador'           => 'nullable|string',
            'data.talla_panal'                  => 'nullable|numeric',
            'data.nombre_cuidador'              => 'nullable|string', // Requiered if in form has_cuidador is true
            'data.apellido_paterno_cuidador'    => 'nullable|string', // Requiered if in form has_cuidador is true
            'data.apellido_materno_cuidador'    => 'nullable|string', // Requiered if in form has_cuidador is true
            'data.fecha_nacimiento_cuidador'    => 'nullable|date', // Requiered if in form has_cuidador is true
            'data.run_cuidador'                 => 'nullable|numeric', // Requiered if in form has_cuidador is true
            'data.dv_cuidador'                  => 'nullable|numeric', // Requiered if in form has_cuidador is true
            'data.sexo_cuidador'                => 'nullable|string', // Requiered if in form has_cuidador is true
            'data.genero_cuidador'              => 'nullable|string',
            'data.nacionalidad_cuidador'        => 'nullable|string', // Requiered if in form has_cuidador is true
            'data.parentesco_cuidador'          => 'nullable|string', // Requiered if in form has_cuidador is true
            'data.empam_cuidador'               => 'nullable|boolean',
            'data.zarit_cuidador'               => 'nullable|boolean',
            'data.inmunizaciones_cuidador'      => 'nullable|date',
            'data.plan_elaborado_cuidador'      => 'nullable|boolean',
            'data.plan_evaluado_cuidador'       => 'nullable|boolean',
            'data.capacitacion_cuidador'        => 'nullable|boolean',
            'data.estipendio_cuidador'          => 'nullable|boolean',
        ]);

        $importer = app()->make(ConditionImporter::class);
        $importer->processRow($data);

        session()->flash('message', 'Dependent User created successfully.');
    }
}
