<div class="form-row mb-3">
    <fieldset class="col-md-2">
        <label for="for-cod">Código*</label>
        <input type="text" wire:model="code" class="form-control">
        @error('code') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="col-md-2">
        <label for="for-name">Nombre*</label>
        <input type="text" wire:model="name" class="form-control">
        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="col-md-2">
        <label for="for-valid-from">Valido desde*</label>
        <input type="date" wire:model="valid_from" class="form-control">
        @error('valid_from') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="col-md-2">
        <label for="for-valid-to">Válido hasta</label>
        <input type="date" wire:model="valid_to" class="form-control">
        @error('valid_to') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="col-md-2">
        <label for="for-valid-to">Valor*</label>
        <input type="number" wire:model="value" class="form-control">
        @error('value') <span class="text-danger">{{ $message }}</span> @enderror
    </fieldset>
</div>