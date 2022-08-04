@extends('layouts.app')

@section('content')

<h3 class="mb-3">Añadir Campos a Solicitud de Chagas N°{{$suspectCase->id??''}}</h3>

<form method="POST" class="form-horizontal" action="{{ route('epi.chagas.update',$suspectCase) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="form-row">
        <fieldset class="form-group col-10 col-md-3">
            <input type="hidden" class="form-control" id="for_id" name="patient_id" value="{{$suspectCase->patient->id}}">

            
            <input type="hidden" class="form-control" id="for_id" name="type" value="Chagas">
            <label for="for_run">Run/Identificación</label>

            <input type="number" max="50000000" class="form-control" id="for_run" name="run" value="{{$suspectCase->patient->Identification->value}}" readonly>
        </fieldset>
        @if($suspectCase->patient->identifierRun)
        <fieldset class="form-group col-2 col-md-1">
            <label for="for_dv">DV</label>
            <input type="text" class="form-control" id="for_dv" name="dv" value="{{$suspectCase->patient->identifierRun->dv}}" readonly>
        </fieldset>
        @endif
        <!-- comentando para que vea sick -->

        <fieldset class="form-group col-12 col-md-3">
            <label for="for_other_identification">Otra identificación</label>
            <input type="text" class="form-control" id="for_other_identification" placeholder="Extranjeros sin run" name="other_identification" readonly>
        </fieldset>

        <fieldset class="form-group col-6 col-md-2">
            <label for="for_sex">Sexo</label>
            <select name="sex" id="for_sex" class="form-control sex" readonly>
                <option value="male" {{$suspectCase->patient->actualSex()->value === 'male'? 'selected' : ''}}>Masculino</option>
                <option value="female" {{$suspectCase->patient->actualSex()->value === 'female'? 'selected' : ''}}>Femenino</option>
                <option value="other" {{$suspectCase->patient->actualSex()->value === 'other'? 'selected' : ''}}>Otro</option>
                <option value="unknown" {{$suspectCase->patient->actualSex()->value === 'unknown'? 'selected' : ''}}>Desconocido</option>
            </select>
        </fieldset>

        <fieldset class="form-group col-6 col-md-2">
            <label for="for_birthday">Fecha Nacimiento</label>
            <input type="date" class="form-control" id="for_birthday" name="birthday" value="{{ $suspectCase->patient->birthday }}" readonly required>
        </fieldset>

        <fieldset class="form-group col-2 col-md-1">
            <label for="for_age">Edad</label>
            <input type="number" class="form-control" id="for_age" name="age" value={{\Carbon\Carbon::parse($suspectCase->patient->birthday)->age}} readonly>
        </fieldset>

    </div>


    <div class="form-row">
        <fieldset class="form-group col-12 col-md-4">
            <label for="for_name">Nombres*</label>
            <input type="text" class="form-control" id="for_name" name="name" style="text-transform: uppercase;" autocomplete="off" value="{{ $suspectCase->patient->actualOfficialHumanName->text?? '' }}" readonly>
        </fieldset>

        <fieldset class="form-group col-6 col-md-4">
            <label for="for_fathers_family">Apellido Paterno *</label>
            <input type="text" class="form-control" id="for_fathers_family" name="fathers_family" style="text-transform: uppercase;" autocomplete="off" value="{{ $suspectCase->patient->actualOfficialHumanName->fathers_family }}" readonly>
        </fieldset>

        <fieldset class="form-group col-6 col-md-4">
            <label for="for_mothers_family">Apellido Materno</label>
            <input type="text" class="form-control" id="for_mothers_family" name="mothers_family" autocomplete="off" style="text-transform: uppercase;" value="{{ $suspectCase->patient->actualOfficialHumanName->mothers_family }}" readonly>
        </fieldset>


    </div>

    <hr>

    <div class="form-row">

        <fieldset class="form-group col-6 col-md-3">
            <label for="for_sample_at">Fecha Muestra</label>
            <input type="datetime-local" class="form-control" id="for_sample_at" name="sample_at" value="{{ $suspectCase->sample_at->format('Y-m-d\TH:i:s') }}" required readonly>
        </fieldset>

        <fieldset class="form-group col-6 col-md-3">
            <label for="for_sample_type">Grupo de Pesquiza</label>
            <select name="research_group" id="for_research_group" class="form-control" readonly>
                <option value="Control Pre concepcional" {{$suspectCase->research_group === 'Control Pre concepcional'? 'selected' : ''}}>Control Pre concepcional</option>
                <option value="Gestante (+semana gestacional)" {{$suspectCase->research_group === 'Gestante (+semana gestacional)'? 'selected' : ''}}>Gestante (+semana gestacional)</option>
                <option value="Estudio de contacto" {{$suspectCase->research_group === 'Estudio de contacto'? 'selected' : ''}}>Estudio de contacto</option>
                <option value="Morbilidad (cualquier persona)" {{$suspectCase->research_group === 'Morbilidad (cualquier persona)'? 'selected' : ''}}>Morbilidad (cualquier persona)</option>
            </select>
        </fieldset>

        @if($suspectCase->research_group === 'Gestante (+semana gestacional)')
        <fieldset class="form-group col-2 col-md-1">
            <label for="newborn_week">Semanas</label>
            <input type="number" class="form-control" id="newborn_week" name="newborn_week" readonly value="{{$suspectCase->newborn_week}}">
        </fieldset>
        @endif

        <fieldset class="form-group col-12 col-md-4">
            <label for="for_establishment_id">Establecimiento*</label>
            <select name="organization_id" id="for_organization_id" class="form-control" readonly required>
                <option value="">Seleccionar Establecimiento</option>
                @foreach($organizations as $organization)
                <option value="{{$organization->id}}" {{ ($organization->id == $suspectCase->organization_id)?'selected':'' }}>{{$organization->alias??''}}</option>
                @endforeach

            </select>
        </fieldset>
    </div>
    @can('Epi: Add Value')
    <div class="form-row">

        <fieldset class="form-group col-6 col-md-6">
            <label for="for_observation">Observación</label>
            <input type="text" class="form-control" name="observation" id="for_observation" value="{{$suspectCase->observation??''}}" readonly>
        </fieldset>






        <!-- <fieldset class="form-group col-6 col-md-6">
            <label for="for_epivigila">Epivigila</label>
            <input type="number" class="form-control" id="for_epivigila" name="epivigila" value="{{$suspectCase->epivigila??''}}" readonly>
        </fieldset> -->


    </div>

    
    <div class="form-row">

        <fieldset class="form-group col-6 col-md-3 alert-warning">
            <label for="for_chagas_result_screening_at">Fecha Resultado Tamizaje</label>
            <input type="datetime-local" class="form-control" id="for_chagas_result_screening_at" name="chagas_result_screening_at" 
            max="{{ date('Y-m-d\TH:i:s') }}"
            value="{{ $suspectCase->chagas_result_screening_at? $suspectCase->chagas_result_screening_at->format('Y-m-d\TH:i:s'):'' }}"            
            >
        </fieldset>

        <fieldset class="form-group col-6 col-md-3 alert-warning">
            <label for="for_chagas_result_screening">Resultado Tamizaje</label>
            <select name="chagas_result_screening" id="for_chagas_result_screening" class="form-control">
                <option value=""></option>
                <option value="Negativo" {{$suspectCase->chagas_result_screening === 'Negativo'? 'selected' : ''}}>Negativo</option>
                <option value="En Proceso" {{$suspectCase->chagas_result_screening === 'En Proceso'? 'selected' : ''}}>En Proceso</option>
                <option value="Pendiente" {{$suspectCase->chagas_result_screening === 'Pendiente'? 'selected' : ''}}>Pendiente</option>
                <option value="Registra muestra anterior" {{$suspectCase->chagas_result_screening === 'Registra muestra anterior'? 'selected' : ''}}>Registra muestra anterior</option>
                <option value="Rechazo" {{$suspectCase->chagas_result_screening === 'Rechazo'? 'selected' : ''}}>Rechazo</option>
            </select>
        </fieldset>

    </div>

    <div class="form-row">

        <fieldset class="form-group col-6 col-md-3 alert-danger">
            <label for="for_pcr_sars_cov_2_at">Fecha Resultado Confirmación</label>
            <input type="datetime-local" class="form-control" id="for_chagas_result_confirmation_at" name="chagas_result_confirmation_at"
            max="{{ date('Y-m-d\TH:i:s') }}"
            value="{{ $suspectCase->chagas_result_confirmation_at? $suspectCase->chagas_result_confirmation_at->format('Y-m-d\TH:i:s'):'' }}"
            
            >
        </fieldset>

        <fieldset class="form-group col-6 col-md-3 alert-danger">
            <label for="for_chagas_result_confirmation">Resultado Confirmación</label>
            <select name="chagas_result_confirmation" id="for_chagas_result_confirmation" class="form-control">
                <option value=""></option>
                <option value="Negativo" {{$suspectCase->chagas_result_confirmation === 'Negativo'? 'selected' : ''}}>Negativo</option>
                <option value="Positivo" {{$suspectCase->chagas_result_confirmation === 'Positivo'? 'selected' : ''}}>Positivo</option>
                <option value="No Concluyente" {{$suspectCase->chagas_result_confirmation === 'No Concluyente'? 'selected' : ''}}>No Concluyente</option>
            </select>
        </fieldset>

        @endcan

        @can('SuspectCase: tecnologo')

        <fieldset class="form-group col-12 col-md-12">
            <label for="for_file">Archivo</label>
            <div class="custom-file">
                <input type="file" name="forfile[]" class="custom-file-input" id="forfile" lang="es" multiple>
                <label class="custom-file-label" for="customFileLang">Seleccionar Archivo</label>
            </div>
        </fieldset>





        <hr>

        @endcan
    </div>
    <div class="form-row">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a class="btn btn-outline-secondary " href="{{ route('home') }}">
            Cancelar
        </a>
    </div>





</form></br>


@endsection