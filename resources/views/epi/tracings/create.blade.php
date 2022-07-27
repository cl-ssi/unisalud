@extends('layouts.app')

@section('content')

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
        </div>
        <hr>

        <h4>Notificación</h4>
        <div class="form-row">
            <fieldset class="form-group col-8 col-sm-5 col-md-3 col-lg-2">
                <label for="for_notification_at">Fecha de Notificación</label>
                <input type="date" class="form-control" name="notification_at" id="for_notification_at" value="">
            </fieldset>

            <fieldset class="form-group col-9 col-sm-5 col-md-4 col-lg-3">
                <label for="for_notification_mechanism">Mecanismo de Notificación</label>
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

            <fieldset class="form-group col-8 col-sm-5 col-md-3 col-lg-2">
                <label for="for_discharged_at">Fecha alta médica</label>
                <input type="date" class="form-control" name="discharged_at" id="for_discharged_at" value="">
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

            <fieldset class="form-group col-4 col-sm-3 col-md-2 col-lg-2 order-4 order-lg-5">
                <label for="for_functionary">Func. Salud</label>
                <select name="functionary" id="for_functionary" class="form-control">
                    <option value=""></option>
                    <option value="1">Si</option>
                    <option value="0">No</option>
                </select>
            </fieldset>
        </div>
        <!--**********************************-->
        <div class="form-row">
            <fieldset class="form-group col-md-3">
                <label for="for_symptoms">Síntomas</label>
                <select name="symptoms" id="for_symptoms" class="form-control">
                    <option value=""></option>
                    <option value="0">No</option>
                    <option value="1">Si</option>
                </select>
            </fieldset>

            <fieldset class="form-group col-md-3">
                <label for="for_symptoms_start_at">Inicio de síntomas</label>
                <input type="datetime-local" class="form-control" name="symptoms_start_at" id="for_symptoms_start_at" value="">
            </fieldset>

            <fieldset class="form-group col-md-3">
                <label for="for_symptoms_end_at">Fin de síntomas</label>
                <input type="datetime-local" class="form-control" name="symptoms_end_at" id="for_symptoms_end_at" value="">
            </fieldset>

            <fieldset class="form-group col-md-3">
                <label for="for_risk_rating">Frecuencia de llamado</label>
                <select name="risk_rating" id="for_risk_rating" class="form-control">
                    <option value=""></option>
                    <option value="0">Bajo</option>
                    <option value="1">Medio</option>
                    <option value="2">Alto</option>
                </select>
            </fieldset>
        </div>

        <!--**********************************-->
        <div class="form-row">
            <fieldset class="form-group col-8 col-sm-5 col-md-4 col-lg-3">
                <label for="for_quarantine_start_at">Inicio Cuarentena *</label>
                <input type="date" class="form-control" name="quarantine_start_at" id="for_quarantine_start_at" required value="">
            </fieldset>

            <fieldset class="form-group col-8 col-sm-5 col-md-4 col-lg-3">
                <label for="for_quarantine_end_at">Término de Cuarentena *</label>
                <input type="date" class="form-control" name="quarantine_end_at" id="for_quarantine_end_at" required value="">
            </fieldset>

            <fieldset class="form-group col-12 col-sm-10 col-md-4 col-lg-4">
                <label for="for_cannot_quarantine">No puede realizar cuarentena</label>
                <input type="text" class="form-control" name="cannot_quarantine" id="for_cannot_quarantine" value="">
            </fieldset>
        </div>

        <!--**********************************-->
        <div class="form-row">
            <fieldset class="form-group col-12 col-sm-7 col-md-4">
                <label for="for_responsible_family_member">Familiar responsable / teléfono</label>
                <input type="text" class="form-control" name="responsible_family_member" id="for_responsible_family_member" value="">
            </fieldset>

            <fieldset class="form-group col-8 col-sm-5 col-md-3 col-lg-2">
                <label for="for_prevision">Previsión</label>
                <select name="prevision" id="for_prevision" class="form-control">
                    <option value=""></option>
                    <option value="Sin prevision">
                        Sin prevision
                    </option>
                    <option value="Fonasa A">
                        Fonasa A
                    </option>
                    <option value="Fonasa B">
                        Fonasa B
                    </option>
                    <option value="Fonasa C">
                        Fonasa C
                    </option>
                    <option value="Fonasa D">
                        Fonasa D
                    </option>
                    <option value="Isapre">
                        Isapre
                    </option>
                    <option value="Otro">
                        Otro
                    </option>
                </select>
            </fieldset>

            <fieldset class="form-group col-4 col-sm-3 col-md-2 col-lg-2">
                <label for="for_gestation">Gestante</label>
                <select name="gestation" id="for_gestation" class="form-control">
                    <option value=""></option>
                    <option value="0">No</option>
                    <option value="1">Si</option>
                </select>
            </fieldset>

            <fieldset class="form-group col-5 col-sm-4 col-md-3 col-lg-3">
                <label for="for_gestation_week">Semanas de gestación</label>
                <input type="number" class="form-control" name="gestation_week" id="for_gestation_week" value="">
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
                        <textarea class="form-control" name="indications"
                                  id="for_indications" rows="4"></textarea>
                    </fieldset>

                    <fieldset class="form-group col-12 col-sm-6">
                        <label for="for_observations">Observaciones</label>
                        <textarea class="form-control" name="observations"
                                  id="for_observations" rows="4"></textarea>
                    </fieldset>

        </div>

    </div>
</div>

@endsection

@section('custom_js')

@endsection