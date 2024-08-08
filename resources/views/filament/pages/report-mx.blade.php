<x-filament-panels::page>
        <div>
            @livewire('list-birards', ['type' => 'mam', 'filters' => $this->filters])
        </div>
        <div>
            @livewire('list-birards', ['type' => 'eco', 'filters' => $this->filters])
        </div>
        <div>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
