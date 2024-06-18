<x-filament-panels::page>
    <!-- Formulario de Filament -->
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    </form>

    <!-- Tabla de Filament -->
    <div class="mt-8">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
