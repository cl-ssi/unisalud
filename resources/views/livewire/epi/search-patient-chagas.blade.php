<div>
    <input type="number" wire:model.debounce.500ms="search" class="form-control" placeholder="run(sindv)/id" min="0" max="99999999">
    <button type="button" wire:click="searchPatient" class="btn btn-primary">Buscar</button>

    @if($message)
        <div>{{ $message }}</div>
    @endif

    @if($suspectcases)
        @foreach($suspectcases as $suspectcase)
            <div>Nombre del paciente encontrado: {{ $suspectcase->patient->given }}</div>
            <input type="hidden" name="mother_id" value="{{ $suspectcase->patient->id }}">
        @endforeach
    @endif
</div>
