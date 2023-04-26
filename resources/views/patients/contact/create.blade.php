@extends('layouts.app')

@section('title', 'Contacto Pacientes')

@section('content')

<h3>Agregar Contacto de Pacientes</h3>

<h5>Paciente:</h5>

<div class="form-row">
    @if($patient->identifierRun)
    <fieldset class="form-group col-md-3">
        <label for="for_register_at">RUN</label>
        <input type="text" class="form-control" name="register_at" id="for_register_at" value="{{ $patient->identifierRun->value }}-{{$patient->identifierRun->dv}}" style="text-transform: uppercase;" readonly>
    </fieldset>    
    @else
    <fieldset class="form-group col-md-3">
        <label for="for_register_at">Pasaporte u Otra Identificacion</label>
        <input type="text" class="form-control" value="{{$patient->Identification->value ??''}}" style="text-transform: uppercase;" readonly>
    </fieldset>    
    @endif

    <fieldset class="form-group col-md-3">
        <label for="for_register_at">Nombre</label>
        <input type="text" class="form-control" value="{{ $patient->given ??'' }}" style="text-transform: uppercase;" readonly>
    </fieldset>

    <fieldset class="form-group col-md-3">
        <label for="for_fathers_family">Apellido Paterno</label>
        <input type="text" class="form-control" value="{{ $patient->fathers_family }}" style="text-transform: uppercase;" readonly>
    </fieldset>

    <fieldset class="form-group col-md-3">
        <label for="for_mothers_family">Apellido Materno</label>
        <input type="text" class="form-control" value="{{ $patient->mothers_family }}" style="text-transform: uppercase;" readonly>
    </fieldset>
</div>

<hr>

<h5>Busqueda de Contacto:</h5>
@livewire('patient-advanced-search', ['original_id' => $patient->id])
@endsection