<div class="form-row mb-3">
    <fieldset class="col-md-2">
        <label for="for-name">Nombre*</label>
        <input type="text" wire:model="name" class="form-control">
        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="col-md-4">
        <label for="for-description">Descripción*</label>
        <input type="text" wire:model="description" class="form-control">
        @error('description') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="col-md-2">
        <label for="for-valid-from">Valido desde*</label>
        <input type="date" wire:model="valid_from" class="form-control">
        @error('valid_from') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="col-md-2">
        <label for="for-valid-to">Válido hasta</label>
        <input type="date" wire:model="valid_to" value="" class="form-control">
        @error('valid_to') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="col-md-2">
        <label for="for-valid-to">Estado*</label>
        <select wire:model="status" class="form-control">
            <option value="0">Inactivo</option>
            <option value="1">Activo</option>
        </select>
        @error('value') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>
    
</div>