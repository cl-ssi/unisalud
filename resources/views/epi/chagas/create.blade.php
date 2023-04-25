@extends('layouts.app')

@section('content')

<h3 class="mb-3">Nueva Solicitud de Chagas</h3>


<form method="POST" class="form-horizontal" action="{{ route('epi.chagas.store') }}" enctype="multipart/form-data">
    @csrf
    @method('POST')
    <div class="form-row">
        <fieldset class="form-group col-6 col-md-2">
            <input type="hidden" class="form-control" id="for_id" name="patient_id" value="{{$user->id}}">
            <input type="hidden" class="form-control" id="for_id" name="type" value="Chagas">
            <label for="for_run">Run</label>
            <input type="number" max="50000000" class="form-control" id="for_run" name="run" value="{{$user->Identification->value}}" readonly>
        </fieldset>

        @if($user->IdentifierRun)
        <fieldset class="form-group col-1 col-md-1">
            <label for="for_dv">DV</label>
            <input type="text" class="form-control" id="for_dv" name="dv" value="{{$user->identifierRun->dv}}" readonly>
        </fieldset>
        @endif

        <fieldset class="form-group col-12 col-md-2">
            <label for="for_other_identification">Otra identificación</label>
            <input type="text" class="form-control" id="for_other_identification" placeholder="Extranjeros sin run" name="other_identification" readonly>
        </fieldset>

        <fieldset class="form-group col-6 col-md-2">
            <label for="for_sex">Sexo</label>
            <select name="sex" id="for_sex" class="form-control sex" readonly>
                <option value="male" {{$user->actualSex === 'Masculino'? 'selected' : ''}}>Masculino</option>
                <option value="female" {{$user->actualSex === 'Femenino'? 'selected' : ''}}>Femenino</option>
                <option value="other" {{$user->actualSex === 'Otro'? 'selected' : ''}}>Otro</option>
                <option value="unknown" {{$user->actualSex === 'Desconocido'? 'selected' : ''}}>Desconocido</option>
            </select>
        </fieldset>

        <fieldset class="form-group col-5 col-md-2">
            <label for="for_birthday">Fecha Nacimiento</label>
            <input type="date" class="form-control" id="for_birthday" name="birthday" value="{{ $user->birthday?$user->birthday->format('Y-m-d'):'' }}" readonly required>
        </fieldset>

        <fieldset class="form-group col-1 col-md-1">
            <label for="for_age">Edad</label>
            <input type="number" class="form-control" id="for_age" name="age" value={{\Carbon\Carbon::parse($user->birthday)->age}} readonly>
        </fieldset>

        <fieldset class="form-group col-2 col-md-2">
            <label for="for_nationality">Nacionalidad</label>
            <input type="text" class="form-control" id="for_nationality" name="nationality" value={{$user->nationality->name}} readonly>
        </fieldset>

    </div>


    <div class="form-row">
        <fieldset class="form-group col-12 col-md-4">
            <label for="for_name">Nombres *</label>
            <input type="text" class="form-control" id="for_name" name="name" style="text-transform: uppercase;" autocomplete="off" value="{{ $user->given?? '' }}" readonly>
        </fieldset>

        <fieldset class="form-group col-6 col-md-4">
            <label for="for_fathers_family">Apellido Paterno *</label>
            <input type="text" class="form-control" id="for_fathers_family" name="fathers_family" style="text-transform: uppercase;" autocomplete="off" value="{{ $user->actualOfficialHumanName->fathers_family }}" readonly>
        </fieldset>

        <fieldset class="form-group col-6 col-md-4">
            <label for="for_mothers_family">Apellido Materno</label>
            <input type="text" class="form-control" id="for_mothers_family" name="mothers_family" autocomplete="off" style="text-transform: uppercase;" value="{{ $user->actualOfficialHumanName->mothers_family }}" readonly>
        </fieldset>


    </div>

    <hr>

    <div class="form-row">

        <fieldset class="form-group col-6 col-md-3">
            <label for="for_sample_at">Fecha Muestra</label>
            <input type="datetime-local" class="form-control" id="for_sample_at" name="sample_at" value="{{ date('Y-m-d\TH:i:s') }}" required min="{{ date('Y-m-d\TH:i:s', strtotime('-2 week')) }}" max="{{ date('Y-m-d\TH:i:s') }}">
        </fieldset>

        <fieldset class="form-group col-12 col-md-4">
            <label for="for_establishment_id">Establecimiento*</label>
            <select name="organization_id" id="for_organization_id" class="form-control" required>
                <option value="">Seleccionar Establecimiento</option>
                @foreach($organizations as $organization)
                <option value="{{$organization->id}}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>{{$organization->alias??''}}</option>
                @endforeach
            </select>
        </fieldset>
    </div>

    <div class="form-row">
        <fieldset class="form-group col-4 col-md-2">
            <label for="for_sample_type">Grupo de Pesquisa</label>
            <select name="research_group" id="research_group" class="form-control" required>
                <option value=""></option>
                <option value="Control Pre concepcional" {{ old('research_group') == 'Control Pre concepcional' ? 'selected' : '' }}>Control Pre concepcional</option>
                <option value="Gestante (+semana gestacional)" {{ old('research_group') == 'Gestante (+semana gestacional)' ? 'selected' : '' }}>Gestante (+semana gestacional)</option>
                <option value="Estudio de contacto" {{ old('research_group') == 'Estudio de contacto' ? 'selected' : '' }}>Estudio de contacto</option>
                <option value="Morbilidad (cualquier persona)" {{ old('research_group') == 'Morbilidad (cualquier persona)' ? 'selected' : '' }}>Morbilidad (cualquier persona)</option>
                <option value="Transmisión Vertical" {{ old('research_group') == 'Transmisión Vertical' ? 'selected' : '' }}>Transmisión Vertical</option>
                <option value="Control Chagas Crónico" {{ old('research_group') == 'Control Chagas Crónico' ? 'selected' : '' }}>Control Chagas Crónico</option>
            </select>
        </fieldset>

        <fieldset class="form-group col-2 col-md-1">
            <label for="newborn_week">Semanas</label>
            <input type="number" class="form-control" id="newborn_week" name="newborn_week" min="2" max="44" value="{{ old('newborn_week') }}" disabled>
        </fieldset>
    </div>

    <div class="form-row">
        <div id="seccion_transmision_vertical" style="display:none;">
            <fieldset class="form-group">
                <label for="mothers_run">Madre</label>
                @livewire('epi.search-patient-chagas')
            </fieldset>
        </div>
    </div>

    <div class="form-row">
        <fieldset class="form-group col-6 col-md-6">
            <label for="for_observation">Observación</label>
            <input type="text" class="form-control" name="observation" id="for_observation" autocomplete="off" value="{{ old('observation') }}"  >
        </fieldset>
    </div>


    <button type="submit" class="btn btn-primary">Guardar</button>

    <a class="btn btn-outline-secondary" href="#">
        Cancelar
    </a>
</form><br>

@endsection

@section('custom_js')
<script src='{{asset("js/jquery.rut.chileno.js")}}'></script>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('input[name=run]').keyup(function(e) {
            var str = $("#for_run").val();
            $('#for_dv').val($.rut.dv(str));
        });

    });
</script>

<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/defaults-es_CL.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // función para manejar el cambio de valor y la carga inicial
        function handleValueChange() {
            var value = $('#research_group').val();

            //código para Gestante
            if (value == "Gestante (+semana gestacional)") {
                $('#newborn_week').removeAttr('disabled');
                $("#newborn_week").prop('required', true);
            } else {
                $('#newborn_week').attr('disabled', 'disabled');
                $("#newborn_week").prop('required', false);
            }

            //condicional para mostrar la sección oculta que se muestra solo con Transmisión Vertical
            if (value == "Transmisión Vertical") {
                $('#seccion_transmision_vertical').show();
            } else {
                $('#seccion_transmision_vertical').hide();
            }
        }

        // llamar a la función para manejar la carga inicial
        handleValueChange();

        // llamar a la función para manejar el cambio de valor
        $('#research_group').on('change', handleValueChange);
    });
</script>

@endsection