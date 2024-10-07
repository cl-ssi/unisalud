<x-filament::page>
    <div>
        @livewire('condition.info-user', ['user_id' => $user_id])
    </div>

    <!-- Formulario de Filament -->
    <div class="space-y-4">
        {{ $this->form }}

    </div>
    @if ($condition_id)
    <div>
        @livewire(\App\Filament\Widgets\Condition\DependentUserMapWidget::class, ['condition_id' => $condition_id, 'user_id' => $user_id])
    </div>
    @endif

</x-filament::page>
