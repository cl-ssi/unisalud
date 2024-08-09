<x-filament-panels::page>
        <div>
            @livewire('report-mx.form-birards')
        </div>
        <div>
            @livewire('report-mx.list-birards', ['type' => 'mam', 'filters' => $this->filters], key('mam'))
        </div>
        <div>
            @livewire('report-mx.list-birards', ['type' => 'eco', 'filters' => $this->filters], key('eco'))
        </div>
        <div>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
