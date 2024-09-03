<x-filament::page>
    <div>
        @livewire('condition.info-user', ['user_id' => $user_id])
    </div>

    <!-- Formulario de Filament -->
    <div class="space-y-4">
        {{ $this->form }}
    </div>

    <!-- Contenedor del mapa de Google -->
    <gmp-map center="-20.2503,-70.1223" zoom="13" map-id="DEMO_MAP_ID" class="map-rounded">
        @foreach ($users as $user)
            <gmp-advanced-marker position="{{ $user['latitude'] }}, {{ $user['longitude'] }}"
                title="{{ $user['name'] }} - {{ $user['diagnostico'] }}"></gmp-advanced-marker>
        @endforeach
    </gmp-map>

    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('google.api_key') }}&callback=initMap&libraries=marker&v=beta"
        defer></script>

    <!-- Estilo CSS para esquinas redondeadas -->
    <style>
        .map-rounded {
            height: 700px;
            border-radius: 15px;
            overflow: hidden;
        }
    </style>
</x-filament::page>
