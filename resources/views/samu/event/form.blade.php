<h4>Asignación de seguimiento y horarios</h4>
<div class="form-row">

    <fieldset class="form-group col-md-3">
        <label for="for-key">Clave*</label>
        <select class="form-control @error('key_id') is-invalid @enderror" name="key_id" id="for-key" required>
            <option value="">Selecciona una Clave</option>
            @foreach($keys as $key)
            <option
                value="{{ $key->id }}"
                {{ old('key_id', $call ? optional($call)->key_id : optional($event)->key_id) == $key->id ? 'selected' : '' }}
            >
                {{ $key->key }}  - {{ $key->name }}
            </option>
            @endforeach
        </select>
        @error('key_id')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-md-3">
        <label for="for-return-key">Clave de Retorno</label>
        <select class="form-control @error('return_key_id') is-invalid @enderror" name="return_key_id" id="for-return-key">
            <option value="">Selecciona una Clave de Retorno</option>
            @foreach($keys as $key)
            <option value="{{ $key->id }}" {{ old('return_key_id', optional($event)->return_key_id) == $key->id ? 'selected' : '' }}>
                {{ $key->key }}  - {{ $key->name }}
            </option>
            @endforeach
        </select>
        @error('return_key_id')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>


    <fieldset class="form-group col-md-6">
        <label for="for-observation">Observación</label>
        <input type="text" class="form-control @error('observation') is-invalid @enderror" name="observation" id="for-observation" value="{{ old('observation', optional($event)->observation) }}">
        @error('observation')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>
</div>

<div class="form-row">

    <fieldset class="form-group col-md-3">
        <label for="for-mobile">Móvil*</label>
        <select class="form-control @error('mobile_id') is-invalid @enderror" name="mobile_id" id="for-mobile" required>
            <option value="">Selecciona un Móvil</option>
            @foreach($mobilesInService as $mis)
            <option value="{{ $mis->mobile->id }}" {{ old('mobile_id', optional($event)->mobile_id) == $mis->mobile->id ? 'selected' : '' }}>
                {{ $mis->mobile->code }} {{ $mis->mobile->name }} - {{ optional($mis->type)->name }} (PROPIO)
                {{ ($mis->isHavingLunch()) ? ' - (COLACIÓN)' : '' }}
            </option>
            @endforeach
            @foreach($mobiles as $mobile)
            <option value="{{ $mobile->id }}" {{ old('mobile_id', optional($event)->mobile_id) == $mobile->id ? 'selected' : '' }}>
                {{ $mobile->code }} {{ $mobile->name }}
            </option>
            @endforeach
        </select>
        @error('mobile_id')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-md-9">
        <label for="for-external-crew">Tripulación externa (si el móvil no pertenece a SAMU)</label>
        <input type="text" class="form-control @error('external_crew') is-invalid @enderror" name="external_crew" id="for-external-crew" value="{{ old('external_crew', optional($event)->external_crew) }}">
        @error('external_crew')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

</div>

<div class="form-row">
    <!-- Is used for the rule -->
    <input type="hidden" name="timestampFormat" value="{{ $timestampFormat }}">

    <fieldset class="form-group {{ $inputType == 'time' ? 'col-md-1' : 'col-md-3' }}">
        <label for="for-departure-at">Aviso @if($inputType == 'time') <br> @endif salida</label>
        <input
            type="{{ $inputType }}"
            class="form-control @error('departure_at') is-invalid @enderror"
            name="departure_at"
            id="for-departure-at"
            value="{{ ( $event && $event->departure_at) ? $event->departure_at->format($timestampFormat) : old('departure_at') }}"
        >

        @error('departure_at')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group {{ $inputType == 'time' ? 'col-md-1' : 'col-md-3' }}">
        <label for="for-mobile-departure-at">Salida @if($inputType == 'time') <br> @endif móvil</label>
        <input
            type="{{ $inputType }}"
            class="form-control @error('mobile_departure_at') is-invalid @enderror"
            name="mobile_departure_at"
            id="for-mobile-departure-at"
            value="{{ ( $event &&  $event->mobile_departure_at)? $event->mobile_departure_at->format($timestampFormat) : old('mobile_departure_at') }}"
        >
        @error('mobile_departure_at')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group {{ $inputType == 'time' ? 'col-md-1' : 'col-md-3' }}">
        <label for="for-mobile-arrival-at">Llegada @if($inputType == 'time') <br> @endif al lugar</label>
        <input
            type="{{ $inputType }}"
            class="form-control @error('mobile_arrival_at') is-invalid @enderror"
            name="mobile_arrival_at"
            id="for-mobile-arrival-at"
            value="{{ ( $event &&  $event->mobile_arrival_at)? $event->mobile_arrival_at->format($timestampFormat) : old('mobile_arrival_at') }}"
        >
        @error('mobile_arrival_at')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group {{ $inputType == 'time' ? 'col-md-1' : 'col-md-3' }}">
        <label for="for-route-to-healtcenter-at">Ruta @if($inputType == 'time') <br> @endif c.asistencial</label>
        <input
            type="{{ $inputType }}"
            class="form-control @error('route_to_healtcenter_at') is-invalid @enderror"
            name="route_to_healtcenter_at"
            id="for-route-to-healtcenter-at"
            value="{{ ( $event &&  $event->route_to_healtcenter_at)? $event->route_to_healtcenter_at->format($timestampFormat) : old('route_to_healtcenter_at') }}"
        >
        @error('route_to_healtcenter_at')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group {{ $inputType == 'time' ? 'col-md-1' : 'col-md-3' }}">
        <label for="for-healthcenter-at">Centro @if($inputType == 'time') <br> @endif asistencial</label>
        <input
            type="{{ $inputType }}"
            class="form-control @error('healthcenter_at') is-invalid @enderror"
            name="healthcenter_at"
            id="for-healthcenter-at"
            value="{{ ( $event &&  $event->healthcenter_at)? $event->healthcenter_at->format($timestampFormat) : old('healthcenter_at') }}"
        >
        @error('healthcenter_at')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group {{ $inputType == 'time' ? 'col-md-1' : 'col-md-3' }}">
        <label for="for-patient-reception-at">Recepción @if($inputType == 'time') <br> @endif de pcte</label>
        <input
            type="{{ $inputType }}"
            class="form-control @error('patient_reception_at') is-invalid @enderror"
            name="patient_reception_at"
            id="for-patient-reception-at"
            value="{{ ( $event &&  $event->patient_reception_at)? $event->patient_reception_at->format($timestampFormat) : old('patient_reception_at') }}"
        >
        @error('patient_reception_at')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group {{ $inputType == 'time' ? 'col-md-1' : 'col-md-3' }}">
        <label for="for-return-base-at">Retorno @if($inputType == 'time') <br> @endif base</label>
        <input
            type="{{ $inputType }}"
            class="form-control @error('return_base_at') is-invalid @enderror"
            name="return_base_at"
            id="for-return-base-at"
            value="{{ ( $event &&  $event->return_base_at)? $event->return_base_at->format($timestampFormat) : old('return_base_at') }}"
        >
        @error('return_base_at')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group {{ $inputType == 'time' ? 'col-md-1' : 'col-md-3' }}">
        <label for="for-on-base-at">Móvil @if($inputType == 'time') <br> @endif en base</label>
        <input
            type="{{ $inputType }}"
            class="form-control @error('on_base_at') is-invalid @enderror"
            name="on_base_at"
            id="for-on-base-at"
            value="{{ ( $event &&  $event->on_base_at)? $event->on_base_at->format($timestampFormat) : old('on_base_at') }}"
        >
        @error('on_base_at')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

</div>


<div class="form-row">

    <fieldset class="form-group col-12 col-md-2">
        <label for="for-commune">Comuna</label>
        <select class="form-control @error('commune_id') is-invalid @enderror" name="commune_id" id="for-commune">
            <option value="">Selecciona una Comuna </option>
            @foreach($communes as $name => $id)
                <option
                    value="{{ $id }}"
                    {{ old('commune_id', $call ? optional($call)->commune_id : optional($event)->commune_id ) == $id ? 'selected' : '' }}
                >
                    {{ $name }}
                </option>
            @endforeach
        </select>
        @error('commune_id')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-12 col-md-3">
        <label for="for-address">Dirección</label>
        <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" id="for-address"
        value="{{ old('address', $call ? optional($call)->address : optional($event)->address )}}">
        @error('address')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-12 col-md-4">
        <label for="for-address-reference">Referencia dirección</label>
        <input
            type="text"
            class="form-control @error('address_reference') is-invalid @enderror"
            name="address_reference" id="for-address-reference"
            value="{{ old('address_reference', $call ? optional($call)->address_reference : optional($event)->address_reference )}}"
            placeholder="Después del semáforo, cerca del puente, entre calle 1 y 2">

        @error('address_reference')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

</div>


<hr>


<h4>Datos del paciente</h4>
<div class="form-row">

    <fieldset class="form-check form-check-inline col-md-1 col-12">
        <input class="form-check-input @error('patient_unknown') is-invalid @enderror" type="checkbox" name="patient_unknown"
            id="patient-unknown" value="1" {{ ( $event &&  $event->patient_unknown)? 'checked' : '' }}>
        <label class="form-check-label" for="patient-unknown">No identificado</label>
        @error('patient_unknown')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-md-3">
        <label for="for-patient-name">Nombre del paciente</label>
        <input type="text" class="form-control @error('patient_name') is-invalid @enderror" name="patient_name" id="for-patient-name"
            value="{{ old('patient_name', optional($event)->patient_name) }}">
        @error('patient_name')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-12 col-md-2">
        <label for="for-identifier-type">Tipo de identificación</label>
        <select class="form-control @error('patient_identifier_type_id') is-invalid @enderror" name="patient_identifier_type_id" id="for-identifier-type">
            <option value="">Seleccione Tipo identificación</option>
            @foreach($identifierTypes as $text => $id)
                <option value="{{ $id }}" {{ old('patient_identifier_type_id', optional($event)->patient_identifier_type_id) == $id ? 'selected' : '' }}>{{ $text }}</option>
            @endforeach
        </select>
        @error('patient_identifier_type_id')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-md-2">
        <label for="for-patient-identification">Identificación</label>
        <input type="text" class="form-control @error('patient_identification') is-invalid @enderror" name="patient_identification" id="for-patient-identification"
            value="{{ old('patient_identification', optional($event)->patient_identification) }}"
            placeholder="sin puntos ni guión">
        @error('patient_identification')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

</div>


<hr>

<h4>Evaluación</h4>

<div class="form-row">
    <fieldset class="form-group col-md-12">
        <label for="for-reception-detail">Detalle de recepción</label>
        <textarea class="form-control @error('reception_detail') is-invalid @enderror" rows="6" name="reception_detail" id="for-reception-detail">{{ old('reception_detail', optional($event)->reception_detail) }}</textarea>
        @error('reception_detail')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>
</div>


<div class="form-row">
    <fieldset class="form-group col-md-3 col-12">
        <label for="for-establishment">Establecimiento recepción pcte.</label>
        <select class="form-control @error('establishment_id') is-invalid @enderror" name="establishment_id" id="for-establishment">
            <option value="">Seleccione recepción pcte</option>
            @foreach($establishments as $name => $id)
                <option value="{{ $id }}" {{ old('establishment_id', optional($event)->establishment_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
        @error('establishment_id')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-md-2 col-12">
        <label for="for-establishment-details">Ubicación dentro del establecimiento.</label>
        <input type="text" class="form-control @error('establishment_details') is-invalid @enderror" name="establishment_details" id="for-establishment-details" value="{{ old('establishment_details', optional($event)->establishment_details) }}">
        @error('establishment_details')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-md-3 col-12">
        <label for="for-reception-person">Personal recepción pcte.</label>
        <input type="text" class="form-control @error('reception_person') is-invalid @enderror" name="reception_person" id="for-reception-person" value="{{ old('reception_person', optional($event)->reception_person) }}">
        @error('reception_person')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-12 col-md-2">
        <label for="for-rau">Registro Atención Urgencia</label>
        <input type="number" class="form-control @error('rau') is-invalid @enderror" name="rau" id="for-rau" value="{{ old('rau', optional($event)->rau) }}">
        @error('rau')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-md-2">
        <label for="for-reception-place-id">Otro lugar de recepción</label>
        <select class="form-control @error('reception_place_id') is-invalid @enderror" name="reception_place_id" id="for-reception-place-id">
            <option value="">Seleccione Lugar Recepción</option>
            @foreach($receptionPlaces as $name => $id)
                <option value="{{ $id }}" {{ old('reception_place_id', optional($event)->reception_place_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
        @error('reception_place_id')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>


</div>


<hr>

<h4>Asignación Signos Vitales</h4>

    @include('samu.event.partials.vital-sign-form', ['event'=> $event, 'edit' => request()->routeIs('samu.event.edit') ? true : false])

<div class="form-row">
    <fieldset class="form-group col-12 col-md-4">
        <label for="for-treatment">Tratamiento</label>
        <textarea class="form-control @error('treatment') is-invalid @enderror" rows="6" name="treatment" id="for-treatment">{{ old('treatment', optional($event)->treatment) }}</textarea>
        @error('treatment')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>
    <fieldset class="form-group col-12 col-md-8">
        <label for="for-observation-sv">Observación</label>
        <textarea class="form-control @error('observation_sv') is-invalid @enderror" rows="6" name="observation_sv" id="for-observation-sv">{{ old('observation_sv', optional($event)->observation_sv) }}</textarea>
        @error('observation_sv')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>
</div>
<!-- fin de seguimeinto-->