@extends('layouts.app')

@section('content')

<form method="POST" class="form-horizontal" action="{{ route('epi.chagas.store') }}" enctype="multipart/form-data">
    @csrf
    @method('POST')
    
    <input type="hidden" class="form-control" name="patient_id" id="for_patient_id" value="{{$user->id}}">

    <div class="card">
        <div class="card-body">
            <h4>Entrega de Resultado</h4>
            <div class="form-row">
                <fieldset class="form-group col-8 col-sm-5 col-md-3 col-lg-2">
                    <label for="for_notification_at">Fecha de Entrega</label>
                    <input type="date" class="form-control" name="notification_at" id="for_notification_at" value="">
                </fieldset>

                <fieldset class="form-group col-9 col-sm-5 col-md-4 col-lg-3">
                    <label for="for_notification_mechanism">Mecanismo de Entrega</label>
                    <select name="notification_mechanism" id="for_notification_mechanism" class="form-control">
                        <option></option>
                        <option value="Pendiente">
                            Pendiente
                        </option>
                        <option value="Llamada telefónica">
                            Llamada telefónica
                        </option>
                        <option value="Correo electrónico">
                            Correo electrónico
                        </option>
                        <option value="Visita domiciliaria">
                            Visita domiciliaria
                        </option>
                        <option value="Centro de salud">
                            Centro de salud
                        </option>
                        <option value="Carta certificada">
                            Carta certificada
                        </option>
                    </select>
                </fieldset>

                <fieldset class="form-group col-12 col-sm-6">
                    <label for="for_observation">Observación</label>
                    <input type="text" class="form-control" name="observation" id="for_observation" value="" autocomplete="off">
                </fieldset>
            </div>
            <hr>

            <h4>Interconsulta</h4>
            <div class="form-row">
                <fieldset class="form-group col-8 col-sm-5 col-md-3 col-lg-2">
                    <label for="for_notification_at">Fecha de Interconsulta</label>
                    <input type="date" class="form-control" name="notification_at" id="for_notification_at" value="">
                </fieldset>

                <fieldset class="form-group col-4 col-sm-4 col-md-4 col-lg-4 order-1 order-lg-1">
                    <label for="for_index">Policlínico</label>
                    <select name="index" id="for_index" class="form-control" required>
                        <option value=""></option>
                        <option value="Policlínico de Infectología">Policlínico de Infectología</option>
                        <option value="Policlínico de ARO">Policlínico de ARO</option>
                    </select>
                </fieldset>
            </div>

            <hr>

            <h4>Notificación</h4>
            <div class="form-row">
                <fieldset class="form-group col-8 col-sm-5 col-md-3 col-lg-2">
                    <label for="for_notification_at">Fecha de Notificación</label>
                    <input type="date" class="form-control" name="notification_at" id="for_notification_at" value="">
                </fieldset>

                <fieldset class="form-group col-9 col-sm-5 col-md-4 col-lg-3">
                    <label for="for_epivigila">Folio Epivigila</label>
                    <input type="number" name="epivigila" id="for_epivigila" class="form-control" required>
                </fieldset>

                <fieldset class="form-group col-8 col-sm-5 col-md-6 col-lg-2">
                    <label for="for_cie-10">Código CIE-10</label>
                    <select name="cie10" id="for_cie10" class="form-control selectpicker" data-actions-box="true" data-size="10" title="Seleccione..." data-live-search="true">
                        <option value="">Seleccionar</option>
                        @foreach($cie10s as $cie10)
                        <option value="{{$cie10->name}}">{{$cie10->name}}</option>
                        @endforeach
                    </select>
                </fieldset>

            </div>

            <hr>

            <h4 class="mt-4">Ficha de Seguimiento</h4>
            <div class="form-row">
                <fieldset class="form-group col-5 col-sm-3 col-md-2 col-lg-1 order-1 order-lg-1">
                    <label for="for_index">Indice *</label>
                    <select name="index" id="for_index" class="form-control" required>
                        <option value=""></option>
                        <option value="1">Si</option>
                        <option value="0">No</option>
                        <option value="2">Probable</option>
                    </select>
                </fieldset>
                <!--**********************************-->

                <fieldset class="form-group col-12 col-sm-6 col-md-5 col-lg-3 order-2 order-lg-2">
                    <label for="for_next_control_at">Próximo seguimiento *</label>
                    <input type="datetime-local" class="form-control" name="next_control_at" id="for_next_control_at" required value="">
                </fieldset>

                <fieldset class="form-group col-8 col-sm-5 col-md-3 col-lg-2 order-3 order-lg-3">
                    <label for="for_status">Estado seguimiento *</label>
                    <select name="status" id="for_status_seguimiento" class="form-control" required>
                        <option value=""></option>
                        <option value="1">En seguimiento
                        </option>
                        <option value="0">Fin
                            seguimiento
                        </option>
                    </select>
                </fieldset>
                <fieldset class="form-group col-12 col-sm-12 col-md-9 col-lg-4 order-5 order-lg-4">
                    <label for="for_establishment_id">Establecimiento que realiza seguimiento *</label>
                    <select name="establishment_id" id="for_establishment_id" class="form-control" required>
                        <option value=""></option>
                        <option value="CESFAM Videla">CESFAM Videla</option>
                    </select>
                </fieldset>

            </div>

            <!--**********************************-->
            <div class="form-row">
                <fieldset class="form-group col-12 col-sm-7 col-md-4">
                    <label for="for_responsible_family_member">Fecha de último parto</label>
                    <input type="date" class="form-control" name="responsible_family_member" id="for_responsible_family_member">
                </fieldset>

                <fieldset class="form-group col-12 col-sm-7 col-md-4">
                    <label for="for_occupation">Ocupación</label>
                    <input type="text" class="form-control" name="occupation" id="for_occupation">
                </fieldset>

            </div>


            <!--**********************************-->
            <div class="form-row">
                <fieldset class="form-group col-12 col-sm-7 col-md-4">
                    <label for="for_responsible_family_member">Familiar responsable / teléfono</label>
                    <input type="text" class="form-control" name="responsible_family_member" id="for_responsible_family_member" value="">
                </fieldset>
            </div>


            <!--**********************************-->
            <div class="form-row">
                <fieldset class="form-group col-12 col-sm-6">
                    <label for="for_allergies">Alergias</label>
                    <input type="text" class="form-control" name="allergies" id="for_allergies" value="">
                </fieldset>

                <fieldset class="form-group col-12 col-sm-6">
                    <label for="for_common_use_drugs">Farmacos de uso común</label>
                    <input type="text" class="form-control" name="common_use_drugs" id="for_common_use_drugs" value="">
                </fieldset>
            </div>

            <!--**********************************-->
            <div class="form-row">
                <fieldset class="form-group col-12 col-sm-6">
                    <label for="for_morbid_history">Antecedentes Mórbidos</label>
                    <input type="text" class="form-control" name="morbid_history" id="for_morbid_history" value="">
                </fieldset>

                <fieldset class="form-group col-12 col-sm-6">
                    <label for="for_family_history">Antecedentes Familiares</label>
                    <input type="text" class="form-control" name="family_history" id="for_family_history" value="">
                </fieldset>
            </div>

            <!--**********************************-->
            <div class="form-row">
                <fieldset class="form-group col-12 col-sm-6">
                    <label for="for_indications">Indicaciones</label>
                    <textarea class="form-control" name="indications" id="for_indications" rows="4"></textarea>
                </fieldset>

                <fieldset class="form-group col-12 col-sm-6">
                    <label for="for_observations">Observaciones</label>
                    <textarea class="form-control" name="observations" id="for_observations" rows="4"></textarea>
                </fieldset>

            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>

            <a class="btn btn-outline-secondary" href="#">
                Cancelar
            </a>

        </div>
    </div>



</form>

@endsection

@section('custom_js')
<link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-select.min.css') }}">
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>


@endsection