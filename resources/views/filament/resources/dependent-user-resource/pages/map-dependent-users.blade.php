<x-filament-panels::page>

    <div class="space-y-4">
        {{ $this->form }}
    </div>
    <div>
        @livewire('condition.map', ['condition_id' => $condition_id, 'user_id' => $user_id])
    </div>
</x-filament-panels::page>
