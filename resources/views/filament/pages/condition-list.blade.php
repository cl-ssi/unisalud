<x-filament-panels::page>
    <!-- Formulario de Filament -->
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    </form>

    <!-- Tabla de Filament -->
    <div class="mt-8">
        {{ $this->table }}
    </div>

    {{--

    <!-- Contenedor del mapa de Google -->
    <div id="map" style="height: 500px; width: 100%;" class="mt-8 map-rounded" wire:ignore></div>

    <!-- Script de Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('google.api_key') }}&callback=initMap" async defer></script>
    <script>
        function initMap() {
            var iquique = { lat: -20.2133, lng: -70.1523 };
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: iquique
            });
            
            /*
            var marker = new google.maps.Marker({
                position: iquique,
                map: map
            });
            */
        }
    </script>

    <!-- Estilo CSS para esquinas redondeadas -->
    <style>
        .map-rounded {
            height: 500px;
            width: 100%;
            border-radius: 15px; /* Ajusta el valor para cambiar el radio de las esquinas */
            overflow: hidden; /* Asegura que el contenido no se desborde de las esquinas redondeadas */
        }
    </style>

    --}}

</x-filament-panels::page>
