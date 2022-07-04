@extends('layouts.app')

@section('content')

<h3 class="mb-3">Nueva Solicitud de Chagas</h3>


<form method="POST" class="form-horizontal" action="{{ route('epi.chagas.store') }}" enctype="multipart/form-data">
    @csrf
    @method('POST')
    <div class="form-row">
        <fieldset class="form-group col-10 col-md-3">
            <input type="hidden" class="form-control" id="for_id" name="patient_id" value="{{$user->id}}">

            <input type="hidden" class="form-control" id="for_id" name="type" value="Chagas">
            <label for="for_run">Run</label>
            <input type="number" max="50000000" class="form-control" id="for_run" name="run" value="{{$user->Identification->value}}" readonly>
        </fieldset>

        @if($user->IdentifierRun)
        <fieldset class="form-group col-2 col-md-1">
            <label for="for_dv">DV</label>
            <input type="text" class="form-control" id="for_dv" name="dv" value="{{$user->identifierRun->dv}}" readonly>
        </fieldset>
        @endif

        <fieldset class="form-group col-12 col-md-3">
            <label for="for_other_identification">Otra identificación</label>
            <input type="text" class="form-control" id="for_other_identification" placeholder="Extranjeros sin run" name="other_identification" readonly>
        </fieldset>

        <fieldset class="form-group col-6 col-md-2">
            <label for="for_sex">Sexo</label>
            <select name="sex" id="for_sex" class="form-control sex" readonly>
                <option value="male" {{$user->sex === 'male'? 'selected' : ''}}>Masculino</option>
                <option value="female" {{$user->sex === 'female'? 'selected' : ''}}>Femenino</option>
                <option value="other" {{$user->sex === 'other'? 'selected' : ''}}>Otro</option>
                <option value="unknown" {{$user->sex === 'unknown'? 'selected' : ''}}>Desconocido</option>
            </select>
        </fieldset>

        <fieldset class="form-group col-6 col-md-2">
            <label for="for_birthday">Fecha Nacimiento</label>
            <input type="date" class="form-control" id="for_birthday" name="birthday" value="{{ $user->birthday }}" readonly required>
        </fieldset>

        <fieldset class="form-group col-2 col-md-1">
            <label for="for_age">Edad</label>
            <input type="number" class="form-control" id="for_age" name="age" value={{\Carbon\Carbon::parse($user->birthday)->age}} readonly>
        </fieldset>

    </div>


    <div class="form-row">
        <fieldset class="form-group col-12 col-md-4">
            <label for="for_name">Nombres *</label>
            <input type="text" class="form-control" id="for_name" name="name" style="text-transform: uppercase;" autocomplete="off" value="{{ $user->actualOfficialHumanName->text?? '' }}" readonly>
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

        <fieldset class="form-group col-6 col-md-3">
            <label for="for_sample_type">Grupo de Pesquisa</label>
            <select name="research_group" id="research_group" class="form-control" requireds>
                <option value=""></option>
                <option value="Control Pre concepcional">Control Pre concepcional</option>
                <option value="Gestante (+semana gestacional)">Gestante (+semana gestacional)</option>
                <option value="Estudio de contacto">Estudio de contacto</option>
                <option value="Morbilidad (cualquier persona)">Morbilidad (cualquier persona)</option>
                <option value="Tranmisión Vertical">Tranmisión Vertical</option>
            </select>
        </fieldset>


        <fieldset class="form-group col-2 col-md-1">
            <label for="newborn_week">Semanas</label>
            <input type="number" class="form-control" id="newborn_week" name="newborn_week" disabled>
        </fieldset>



        <fieldset class="form-group col-12 col-md-4">
            <label for="for_establishment_id">Establecimiento*</label>
            <select name="organization_id" id="for_organization_id" class="form-control" required>
                <option value="">Seleccionar Establecimiento</option>
                @foreach($organizations as $organization)
                <option value="{{$organization->id}}">{{$organization->alias??''}}</option>
                @endforeach

            </select>
        </fieldset>    

    </div>

    @can('SuspectCase: tecnologo')
    <div class="form-row">

        <fieldset class="form-group col-6 col-md-2 alert-warning">
            <label for="for_result_ifd_at">Fecha Resultado IFD</label>
            <input type="date" class="form-control" id="for_result_ifd_at" name="result_ifd_at" max="{{ date('Y-m-d') }}">
        </fieldset>

        <fieldset class="form-group col-6 col-md-2 alert-warning">
            <label for="for_result_ifd">Resultado IFD</label>
            <select name="result_ifd" id="for_result_ifd" class="form-control">
                <option></option>
                <option value="Negativo">Negativo</option>
                <option value="Adenovirus">Adenovirus</option>
                <option value="Influenza A">Influenza A</option>
                <option value="Influenza B">Influenza B</option>
                <option value="Metapneumovirus">Metapneumovirus</option>
                <option value="Parainfluenza 1">Parainfluenza 1</option>
                <option value="Parainfluenza 2">Parainfluenza 2</option>
                <option value="Parainfluenza 3">Parainfluenza 3</option>
                <option value="VRS">VRS</option>
                <option value="No solicitado">No solicitado</option>
            </select>
        </fieldset>


        <fieldset class="form-group col-6 col-md-2 alert-warning">
            <label for="for_subtype">Subtipo</label>
            <select name="subtype" id="for_subtype" class="form-control">
                <option value=""></option>
                <option value="H1N1">H1N1</option>
                <option value="H3N2">H3N2</option>
                <option value="INF B">INF B</option>
            </select>
        </fieldset>

    </div>

    <div class="form-row">

        <fieldset class="form-group col-6 col-md-2 alert-danger">
            <label for="for_pcr_sars_cov_2_at">Fecha Resultado PCR</label>
            <input type="date" class="form-control" id="for_pcr_sars_cov_2_at" name="pcr_sars_cov_2_at" max="{{ date('Y-m-d') }}">
        </fieldset>

        <fieldset class="form-group col-6 col-md-2 alert-danger">
            <label for="for_pcr_sars_cov_2">PCR SARS-Cov2</label>
            <select name="pcr_sars_cov_2" id="for_pcr_sars_cov_2" class="form-control">
                <option value="pending">Pendiente</option>
                <option value="negative">Negativo</option>
                <option value="positive">Positivo</option>
                <option value="rejected">Rechazado</option>
                <option value="undetermined">Indeterminado</option>
            </select>
        </fieldset>

        <fieldset class="form-group col-6 col-md-2">
            <label for="for_sent_external_lab_at">Fecha envío lab externo</label>
            <input type="date" class="form-control" id="for_sent_external_lab_at" name="sent_external_lab_at">
        </fieldset>

        @endcan

        @can('SuspectCase: tecnologo')

        <fieldset class="form-group col-12 col-md-3">
            <label for="for_file">Archivo</label>
            <div class="custom-file">
                <input type="file" name="forfile[]" class="custom-file-input" id="forfile" lang="es" multiple>
                <label class="custom-file-label" for="customFileLang">Seleccionar Archivo</label>
            </div>
        </fieldset>

    </div>

    <hr>

    @endcan

    <div class="form-row">

        <fieldset class="form-group col-6 col-md-6">
            <label for="for_observation">Observación</label>
            <input type="text" class="form-control" name="observation" id="for_observation" autocomplete="off">
        </fieldset>



        {{-- <fieldset class="form-group col-6 col-md-2">
            <label for="for_run_medic">Run Médico Solicitante</label>
            <input type="text" class="form-control" name="run_medic" id="for_run_medic"
                placeholder="Ej: 12345678-9">
        </fieldset> --}}


        <!-- <fieldset class="form-group col-6 col-md-6">
            <label for="for_epivigila">Epivigila</label>
            <input type="number" class="form-control" id="for_epivigila" name="epivigila">
        </fieldset> -->


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
        $('#research_group').on('change', function() {
            var value = this.value;
                if (value == "Gestante (+semana gestacional)") {
                $('#newborn_week').removeAttr('disabled');
                $("#newborn_week").prop('required', true);
                }
                else
                {
                $('#newborn_week').attr('disabled', 'disabled');
                $("#newborn_week").prop('required', false);
                }
        });



    });
</script>


@endsection