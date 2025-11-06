<x-filament-panels::page>
    <div class="flex justify-end space-x-2">

        <button
            wire:click="goBack"
            type="button"
            class="inline-flex items-center justify-center h-9 px-3 text-sm font-medium rounded-lg shadow-sm transition duration-75 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 bg-primary-600 text-white hover:bg-primary-500 focus:bg-primary-700 filament-button filament-button-size-md filament-button-color-primary">
            <span class="flex items-center gap-1">
                <span>Volver</span>
            </span>
        </button>
    </div>
    <div class="space-y-4">
        {{ $this->form }}
    </div>
</x-filament-panels::page>