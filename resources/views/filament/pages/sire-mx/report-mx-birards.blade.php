<x-filament-panels::page>
    <div>
        @livewire('sire-mx.form-birards', ['birards' => true])
    </div>
    <div>
    {{ $this->table }}
    </div>
</x-filament-panels::page>
