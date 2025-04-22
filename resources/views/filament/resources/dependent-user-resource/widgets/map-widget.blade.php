<x-filament::widget>
    <x-filament::section>
        <div
            wire:ignore
            x-data="mapComponent(@js($markers))"
            style="height: 500px; z-index: 1;"
        ></div>
    </x-filament::section>
</x-filament::widget>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" integrity="sha256-YU3qCpj/P06tdPBJGPax0bm6Q1wltfwjsho5TR4+TYc=" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" integrity="sha256-YSWCMtmNZNwqex4CEw1nQhvFub2lmU7vcCKP+XVwwXA=" crossorigin="anonymous">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Turf.js/6.5.0/turf.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.min.js"></script>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mapComponent', (markers) => ({
            map: null, // Mapa
            baseLayers: null, // Capas base
            osm: null, // Capa base OSM
            osmHOT: null, // Capa base OSM HOT
            clusterGroup: null, // para generar clusters
            layerControl: null, // Control de capas
            inundacion: null, // Poligono de inundacion
            
            //capas de geojson
            lineaJson: null,
            cotaJson: null,
            iquiqueJson: null,

            init() {
                // Inicializar el mapa
                const zoom = 14;
                const center = [-20.216700, -70.14222];
                const osm = new L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }); 
                const osmHOT = new L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap contributors, Tiles style by Humanitarian OpenStreetMap Team hosted by OpenStreetMap France' });
                this.map = new L.map(this.$el, { center: center, zoom: zoom, layers: [osm] });
                this.clusterGroup = L.markerClusterGroup().addTo(this.map);
                this.layerControl = new L.Control.Layers({ 'OpenStreetMap': osm, 'OpenStreetMap.HOT': osmHOT }).addTo(this.map); 
                this.updateMarkers(markers);

                // Evento para actualizar los marcadores
                window.addEventListener('markersUpdated', event => {
                    const newMarkers = event.detail.markers;
                    this.updateMarkers(newMarkers);
                    if (newMarkers.length) {
                        const { lat, lng } = newMarkers[0];
                        this.map.setView([lat, lng], zoom);
                    }
                });

                // Cargar capas geojson
                Promise.all([
                    fetch("http://unisalud.test/json/linea_seguridad_iquique.geojson").then(res => res.json()),
                    fetch("http://unisalud.test/json/cota_30_tarapaca.geojson").then(res => res.json()),
                    fetch("http://unisalud.test/json/2012_iquique.geojson").then(res => res.json())
                ]).then(([lineaJson, cotaJson, iquiqueJson]) => {
                    this.lineaJson = new L.GeoJSON(lineaJson, { style: { color: '#00FF00' } });
                    this.cotaJson = new L.GeoJSON(cotaJson, { style: { color: '#FF0000' } });
                    this.inundacion = new turf.polygon(cotaJson.features[0].geometry.coordinates);
                    this.iquiqueJson = new L.GeoJSON(iquiqueJson, { style: { color: '#0000FF' } });
                    this.layerControl.addOverlay(new L.LayerGroup([this.lineaJson]), 'Linea de seguridad');
                    this.layerControl.addOverlay(new L.LayerGroup([this.cotaJson]), 'Cota de inundacion');
                    this.layerControl.addOverlay(new L.LayerGroup([this.iquiqueJson]), 'Iquique');
                });
            },

            // Actualizar los marcadores
            async updateMarkers(data) {
                this.clusterGroup.clearLayers();
                await data.forEach(d => {                    
                    const marker = L.marker(
                        [
                            d.lat,
                            d.lng
                        ]
                    );
                    marker.lat = d.lat;
                    marker.lng = d.lng;
                    marker.name = d.name;
                    marker.address = d.address;
                    marker.url = d.url;
                    marker.bindPopup("Cargando...");
                    marker.on('click', this.markerOnClick.bind(this));
                    this.clusterGroup.addLayer(marker);
                });
            },
            // Modificar popup al hacer click en el marcador
            markerOnClick(e){
                let content = `<a href="${e.target.url}" target="_blank">${e.target.name}</a><br>${e.target.address}`;
                if (this.inundacion) {
                    if (turf.booleanPointInPolygon(turf.point([e.target.lng, e.target.lat]), this.inundacion)) {
                        content += '<br> En zona de inundacion';
                    }
                }
                e.target.getPopup().setContent(content);
            }
        }));
    });
    </script>
@endpush