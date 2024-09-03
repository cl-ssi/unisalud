<x-filament-panels::page>
    <div>
        @livewire('condition.info-user', ['user_id' => $user_id])
    </div>
    <x-filament-panels::form>
        {{ $this->form }}
    </x-filament-panels::form>
</x-filament-panels::page>
