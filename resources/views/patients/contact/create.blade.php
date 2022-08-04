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
@livewire('patient-advanced-search')


@endsection

@section('custom_js')

<!-- <script type="text/javascript">
$(document).ready(function(){
    $("select.country").change(function(){
        const selectedcategory = $(this).children("option:selected").val();
        if(selectedcategory ==='family'){
            $('#for_relationship').prop('disabled', false);
            $('#for_relationship').prop("required", true);
        }
        else{
            $('#for_relationship').val('');
            $('#for_relationship').prop('disabled', true);

        }

        if(selectedcategory === 'passenger'){
            $('.mode_of_transport').show();
            $('#for_mode_of_transport').prop("required", true);

            selectedTransportMode = $('#for_mode_of_transport').children("option:selected").val();
            if(selectedTransportMode === 'aereo'){
                $('.flight_name').show();
                $('.flight_date').show();
            }
        }
        else {
            $('.mode_of_transport').hide();
            $('#for_mode_of_transport').prop("required", false);
            $('.flight_name').hide();
            $('.flight_date').hide();
        }

        if(selectedcategory === 'waiting room') {
            $('.waiting_room_establishment').show();
        }
        else {
            $('.waiting_room_establishment').hide();
        }

        if(selectedcategory === 'social'){
            $('.social_meeting_place').show();
            $('.social_meeting_date').show();
        }
        else {
            $('.social_meeting_place').hide();
            $('.social_meeting_date').hide();
        }

        if(selectedcategory === 'ocupational') {
            $('.company_name').show();
        }
        else {
            $('.company_name').hide();
        }

        if(selectedcategory === 'functionary') {
            $('.functionary_profession').show();
        }
        else {
            $('.functionary_profession').hide();
        }

        if(selectedcategory === 'institutional') {
            $('.institution').show();
        }
        else {
            $('.institution').hide();
        }

    });

    $("#for_mode_of_transport").change(function (){
        const selectedMode = $(this).children("option:selected").val();

        if(selectedMode === 'aereo'){
            $('.flight_name').show();
            $('.flight_date').show();
        }
        else {
            $('.flight_name').hide();
            $('.flight_date').hide();
        }
    })

    $('#for_create_tracing').change(function (){
        const selectedOption = $(this).children("option:selected").val();
        if(selectedOption === 'true'){
            $('#for_establishment_id').prop('disabled', false);
            $('#for_establishment_id').prop('required', true);
            $('#for_quarantine_start_at').prop('disabled', false);
            $('#for_quarantine_start_at').prop('required', true);
            $('#for_quarantine_end_at').prop('disabled', false);
            $('#for_quarantine_end_at').prop('required', true);
        }
        else {
            $('#for_establishment_id').prop('disabled', true);
            $('#for_establishment_id').prop('required', false);
            $('#for_quarantine_start_at').prop('disabled', true);
            $('#for_quarantine_start_at').prop('required', false);
            $('#for_quarantine_end_at').prop('disabled', true);
            $('#for_quarantine_end_at').prop('required', false);
        }
    });

    $('#for_last_contact_at').change(function (){
        if(!document.getElementById('for_quarantine_start_at').value){
            const selectedDate = $(this).val().split('T')[0];
            $('#for_quarantine_start_at').val(selectedDate);

            const dateEnd = new Date($(this).val());
            dateEnd.setDate(dateEnd.getDate() + 13)

            $('#for_quarantine_end_at').val(dateEnd.toISOString().split('T')[0]);
        }
    });


});
</script> -->

@endsection