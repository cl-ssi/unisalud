<x-filament-panels::page>
    <div>
        @livewire('condition.info-user', ['user_id' => $user_id])
    </div>
    <form wire:submit="save">
        <x-filament-panels::form>
            {{ $this->form }}
            <x-filament::button type="submit" class="mt-6 w-full">
                <span class="block" wire:loading.class="hidden"> Guardar </span>
                <span class="hidden" wire:loading wire:loading.class="block">Guardando...</span>
            </x-filament::button>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-filament-panels::form>
    </form>
</x-filament-panels::page>
