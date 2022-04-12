<div class="form-row">

    <fieldset class="form-group col-12 col-md-3">
        <label for="for-applicant">Solicitante</label>
        <input type="text" class="form-control form-control @error('applicant') is-invalid @enderror" name="applicant" id="for-applicant"
            value="{{ old('applicant', optional($call)->applicant) }}">
        @error('applicant')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

        <fieldset class="form-group col-12 col-md-2">
        <label for="for-commune">Comuna</label>
        <select class="form-control form-control @error('commune_id') is-invalid @enderror" name="commune_id" id="for-commune">
            <option value="">Selecciona una comuna</option>
            @foreach ($communes as $commune)
            <option value="{{ $commune->id }}" {{ optional($call)->commune_id == $commune->id ? 'selected' : '' }}>
                {{ $commune->name }}
            </option>
            @endforeach
        </select>
        @error('commune_id')
        <div class="text-danger">
            <small>{{ $message }}</small>
        </div>
        @enderror
    </fieldset>
    
    <fieldset class="form-group col-12 col-md-4">
        <label for="for-address">Dirección</label>
        <div class="input-group">
            <input type="text" class="form-control form-control @error('address') is-invalid @enderror" 
                name="address" id="for-address"
                value="{{ old('address', optional($call)->address) }}">
            <div class="input-group-append">
                <button class="btn btn btn-primary" type="button" id="btn-search">
                    <i class="fas fa-map-marker"></i>
                </button>
            </div>
        </div>
        @error('address')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-12 col-md-3">
        <label for="for-address-reference">Referencia dirección</label>
        <input type="text" 
            class="form-control form-control @error('address_reference') is-invalid @enderror" 
            name="address_reference" id="for-address-reference"
            value="{{ old('address_reference', optional($call)->address_reference) }}" 
            placeholder="Después del semáforo, Abajo del puente, Entre calle 1 y 2">
        @error('address_reference')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>



</div>

<div class="form-row">
    <!-- <fieldset class="form-group col-12 col-md-3">
        <label for="for-reason">Motivo</label>
        <input type="reason" class="form-control form-control @error('reason') is-invalid @enderror" name="reason" id="for-reason"
        value="{{ old('reason', optional($call)->reason) }}">
        @error('reason')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset> -->

    <fieldset class="form-group col-12 col-md-2">
        <label for="for-telephone">Teléfono</label>
        <input type="text" class="form-control form-control @error('telephone') is-invalid @enderror" name="telephone" id="for-telephone"
            value="{{ old('telephone', optional($call)->telephone) }}">
        @error('telephone')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-6 col-md-2">
        <label for="for-sex">Sexo</label>
        <select class="form-control form-control @error('sex') is-invalid @enderror" name="sex" id="for-sex">
            <option value="">Selecciona un Sexo</option>
            <option value="MALE" {{ optional($call)->sex == 'MALE' ? 'selected' : '' }}>Masculino</option>
            <option value="FEMALE" {{ optional($call)->sex == 'FEMALE' ? 'selected' : '' }}>Femenino</option>
            <option value="UNKNOWN" {{ optional($call)->sex == 'UNKNOWN' ? 'selected' : '' }}>Indeterminado</option>
            <option value="OTHER" {{ optional($call)->sex == 'OTHER' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('sex')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-6 col-md-1">
        <label for="for-year">Años</label>
        <input type="number" class="form-control form-control @error('year') is-invalid @enderror" name="year" id="for-year"
            value="{{ old('year', optional($call)->year) }}" >
        @error('year')
        <div class="text-danger">
            <small>{{ $message }}</small>
        </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-6 col-md-1">
        <label for="for-month">Meses</label>
        <input type="number" class="form-control form-control @error('month') is-invalid @enderror" name="month" id="for-month"
            value="{{ old('month', optional($call)->month) }}">
        @error('month')
        <div class="text-danger">
            <small>{{ $message }}</small>
        </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-12 col-md-2">
        <label for="for-police_intervention">Intervención de carabineros</label>
        <select class="form-control form-control @error('police_intervention') is-invalid @enderror" name="police_intervention" id="for-police_intervention">
            <option value="">Selecciona una opción</option>
            <option value="1" {{ optional($call)->police_intervention == '1' ? 'selected' : '' }}>Si</option>
            <option value="0" {{ optional($call)->police_intervention == '0' ? 'selected' : '' }}>No</option>
        </select>
        @error('police_intervention')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

</div>

<div class="form-row">

    <fieldset class="form-group col-md-12">
        <label for="for-information">Información telefónica *</label>
        <textarea class="form-control form-control @error('information') is-invalid @enderror" name="information" rows="5" id="for-information" required>{{ old('information', optional($call)->information) }}</textarea>
        @error('information')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

</div>


@if(request()->routeIs('samu.call.edit'))

<div class="form-row">
    <fieldset class="form-group col-md-3">
        <label for="for-classification">Clasificación</label>
        @if($call->classification != 'OT')
        <select class="form-control form-control @error('classification') is-invalid @enderror" name="classification" id="for-classification">
            <option value="">Selecciona una Clasificación</option>
            <option value="T1" {{ old('classification', optional($call)->classification) == 'T1' ? 'selected' : '' }}>T1</option>
            <option value="T2" {{ old('classification', optional($call)->classification) == 'T2' ? 'selected' : '' }}>T2</option>
            <option value="NM" {{ old('classification', optional($call)->classification) == 'NM' ? 'selected' : '' }}>NM</option>
            <option value="OT" {{ old('classification', optional($call)->classification) == 'OT' ? 'selected' : '' }}>OT</option>
        </select>
        <small id="for-classification" class="form-text text-danger">Si hace referencia a otra llamada, no debe clasificarla.</small>
        @else
            <input type="text" class="form-control form-control" name="classification" id="for-classification" readonly
                value="{{ optional($call)->classification }}" >
        @endif
        @error('classification')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-md-3">
        <label for="for-key">Clave</label>
        <select class="form-control form-control" name="key_id" id="for-key">
            <option value="">Selecciona una Clave</option>
            @foreach($keys as $key)
            <option value="{{ $key->id }}" {{ old('key_id', optional($call)->key_id) == $key->id ? 'selected' : '' }}>
                {{ $key->key }} - {{ $key->name }}
            </option>
            @endforeach
        </select>
    </fieldset>

    <fieldset class="form-group col-md-3">
        <label for="for-key">BLS</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <input type="time" name="bls" class="input-group-text" id="bls" step="1" value="{{ old('bls', optional(optional($call)->bls)->format('H:i:s')) }}" readonly>
            </div>
            <div class="input-group-append" id="button-addon4">
                <button class="btn btn-outline-secondary" type="button" onclick="startStop()" id="start">Iniciar</button>
                <button class="btn btn-outline-secondary" type="button" onclick="resetTimer()">Borrar</button>
            </div>

        </div>
        
    </fieldset>

</div>
<div class="form-row">

    <fieldset class="form-group col-md-12">
        <label for="for-regulation">Regulación</label>
        <textarea class="form-control form-control @error('regulation') is-invalid @enderror" name="regulation" rows="5" id="for-regulation">{{ old('regulation', optional($call)->regulation) }}</textarea>
        @error('regulation')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

</div>
@endif

<div class="my-1">
    <button type="submit" class="btn btn-primary">Guardar</button>

    <a href="{{ route('samu.call.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>

<div class="my-3">
    <label for="for-regulation">Mueva el pin para ubicar la llamada en el mapa</label>
    <div id="map"></div>
</div>

<div class="form-row">

    <fieldset class="form-group col-6 col-md-3">
        <label for="latitude">Latitud </label>
        <input type="text" class="form-control form-control @error('latitude') is-invalid @enderror" name="latitude" id="latitude"
            value="{{ old('latitude', optional($call)->latitude) }}">
        @error('latitude')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

    <fieldset class="form-group col-6 col-md-3">
        <label for="longitude">Longitud</label>
        <input type="text" class="form-control form-control @error('longitude') is-invalid @enderror" name="longitude" id="longitude"
            value="{{ old('longitude', optional($call)->longitude) }}">
        @error('longitude')
            <div class="text-danger">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </fieldset>

</div>

<script>
    var x;
    var startstop = 0;

    function startStop() { /* Toggle StartStop */
        startstop = startstop + 1;

        if (startstop === 1) {
            start();
            document.getElementById("start").innerHTML = "Detener";
        } else if (startstop === 2) {
            document.getElementById("start").innerHTML = "Iniciar";
            startstop = 0;
            stop();
        }

    }


    function start() {
        x = setInterval(timer, 10);
    } /* Start */

    function stop() {
        clearInterval(x);
    } /* Stop */

    var milisec = 0;
    var sec = 0; /* holds incrementing value */
    var min = 0;
    var hour = 0;

    /* Contains and outputs returned value of function checkTime */

    var miliSecOut = 0;
    var secOut = 0;
    var minOut = 0;
    var hourOut = 0;

    /* Output variable End */

    function timer() {
        /* Main Timer */

        miliSecOut = checkTime(milisec);
        secOut = checkTime(sec);
        minOut = checkTime(min);
        hourOut = checkTime(hour);

        milisec = ++milisec;

        if (milisec === 100) {
            milisec = 0;
            sec = ++sec;
        }

        if (sec == 60) {
            min = ++min;
            sec = 0;
        }

        if (min == 60) {
            min = 0;
            hour = ++hour;
        }
    
        document.getElementById("bls").value = hourOut + ':' + minOut + ':' + secOut;
    }

    /* Adds 0 when value is <10 */
    function checkTime(i) 
    {
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    }

    function resetTimer() {
        /*Reset*/

        milisec = 0;
        sec = 0;
        min = 0
        hour = 0;

        document.getElementById("bls").value = '';
    }
</script>