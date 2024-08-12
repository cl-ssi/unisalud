<x-filament-panels::page>
    <div>
        @livewire('sire-mx.form-birards')
    </div>
    <div>
        @livewire('sire-mx.list-birards', ['type' => 'mam', 'filters' => $this->filters], key('mam'))
    </div>
    <div>
        @livewire('sire-mx.list-birards', ['type' => 'eco', 'filters' => $this->filters], key('eco'))
    </div>
    <div>
    {{ $this->table }}
    </div>
</x-filament-panels::page>
