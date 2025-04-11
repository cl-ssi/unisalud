<div>
    @push('styles')    
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" integrity="sha256-YU3qCpj/P06tdPBJGPax0bm6Q1wltfwjsho5TR4+TYc=" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" integrity="sha256-YSWCMtmNZNwqex4CEw1nQhvFub2lmU7vcCKP+XVwwXA=" crossorigin="anonymous">
        <style>
            #map { height: 360px; }
        </style>
    @endpush
    @stack('styles')

    qweqwe{{$condition_id}} asdasd
    {{$user_id}}
    <div id="map"></div>
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Turf.js/6.5.0/turf.min.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-ajax/2.1.0/leaflet.ajax.min.js" integrity="sha512-Abr21JO2YqcJ03XGZRPuZSWKBhJpUAR6+2wH5zBeO4wAw4oksr8PRdF+BKIRsxvCdq+Mv4670rZ+dLnIyabbGw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @endpush
    @push('end-scripts')
        <script>
            var map = L.map('map').setView([-20.216700, -70.14222], 14);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);
        
            var markers = L.markerClusterGroup({ animateAddingMarkers: true });

            fetch("http://unisalud.test/json/linea_seguridad_iquique.geojson")
                .then(response => response.json())
                .then(json => L.geoJson(json, {
                    style: function(feature) {
                        return { color: '#00FF00' };
                    },
                    onEachFeature: function(feature, layer) {
                        layer.on('click', function(evt) {
                            // onMapClick(evt);
                        });
                    }
                }).addTo(map)
            );
            fetch("http://unisalud.test/json/cota_30_tarapaca.geojson")
                .then(response => response.json())
                .then(json => L.geoJson(json, {
                    style: function(feature) {
                        return { color: '#FF0000' };
                    },
                    onEachFeature: function(feature, layer) {
                        layer.on('click', function(evt) {
                            // onMapClick(evt);
                        });
                    }
                }).addTo(map)
            );
 
            var myRenderer = L.canvas({ padding: 0.5 });
        </script>
    @endpush
    @stack('scripts')
    @stack('end-scripts')
    
    @foreach ($patients as $patient)

        @if ($patient['latitude'] && $patient['longitude'])
            <script>
                /*
                var marker = L.circleMarker(
                    L.latLng({{ $patient['latitude'] }}, {{ $patient['longitude'] }}),{
                        renderer: myRenderer
                    }).addTo(map).bindPopup("<b>{{ $patient['name'] }}</b><br>{{ $patient['address'] }}");
                */

                markers.addLayer(
                    L.marker(
                        L.latLng({{ $patient['latitude'] }}, {{ $patient['longitude'] }}),{
                        renderer: myRenderer
                    }
                    ).bindPopup("<b>{{ $patient['name'] }}</b><br>{{ $patient['address'] }}")
                );
               
            </script>
        @endif
    @endforeach
    <script>
        map.addLayer(markers);
    </script>
</div>