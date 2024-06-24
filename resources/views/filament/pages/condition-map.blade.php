<x-filament::page>
    <!-- Formulario de Filament -->
    <div class="space-y-4">
        {{ $this->form }}
    </div>

    <!-- Contenedor del mapa de Google -->
    <gmp-map center="-20.2633,-70.1223" zoom="12" map-id="DEMO_MAP_ID" style="height: 500px">
        @foreach ($users as $user)
            <gmp-advanced-marker position="{{ $user['latitude'] }}, {{ $user['longitude'] }}"
                title="{{ $user['name'] }}"></gmp-advanced-marker>
        @endforeach
    </gmp-map>

    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('google.api_key') }}&callback=initMap&libraries=marker&v=beta"
        defer></script>

</x-filament::page>
