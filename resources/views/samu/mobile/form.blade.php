<div class="form-row">

    <fieldset class="form-group col-8 col-md-1">
        <label for="for_mobile_code">Código *</label>
        <input type="text" class="form-control" id="for_mobile_code" name="code" 
            value="{{ ( $mobile &&  $mobile->code) ? $mobile->code : '' }}" required>
    </fieldset>

    <fieldset class="form-group col-8 col-md-3">
        <label for="for_name_mobile_code">Nombre *</label>
        <input type="text" class="form-control" id="for_name_mobile_code" name="name" 
            value="{{ ($mobile && $mobile->name) ? $mobile->name : '' }}" required>
    </fieldset>
    
    <fieldset class="form-group col-8 col-md-1">
        <label for="for_type">Tipo de móvil*</label>
        <select class="form-control" name="mobile_type_id" required>
            <option value=""></option>
            @foreach($types as $id => $name)
                <option value="{{ $id }}" {{ optional($mobile)->type_id === $id ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </fieldset>

    <fieldset class="form-group col-8 col-md-1">
        <label for="for_name_mobile_plate">Patente</label>
        <input type="text" class="form-control" id="for_name_mobile_plate" name="plate" 
            value="{{ ($mobile && $mobile->plate) ? $mobile->plate : '' }}">
    </fieldset>

    <fieldset class="form-group col-8 col-md-3">
        <label for="for_name_mobile_type">Descripción</label>
        <input type="text" class="form-control" id="for_name_mobile_description" name="description" 
            value="{{ ($mobile && $mobile->description) ? $mobile->description : '' }}">
    </fieldset>

    <div class="mt-5 form-check col-md-2">
        <input type="checkbox" class="form-check-input ml-3" name="managed" {{ ($mobile && $mobile->managed) ? 'checked':''}} >
        <label class="form-check-label ml-5" for="exampleCheck1">Móvil Pertenece a Samu</label>
    </div>

    <div class="mt-5 form-check col-md-1">
        <input type="checkbox" class="form-check-input ml-3" name="status" {{ ($mobile && $mobile->status) ? 'checked':''}} >
        <label class="form-check-label ml-5" for="exampleCheck1">Activo</label>
    </div>

</div>