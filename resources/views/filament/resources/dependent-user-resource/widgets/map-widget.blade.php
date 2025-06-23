<x-filament::widget>
    <x-filament::section>
        <div
            wire:ignore
            x-data="mapComponent(@js($markers), @js($baseUrl))"
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
        Alpine.data('mapComponent', (markers, baseUrl) => ({
            map: null, // Leaflet map instance
            baseLayers: null, // Base layers for the map (e.g., OSM, OSM HOT)
            overlayLayers: null, // Overlay layers for additional data (e.g., GeoJSON layers)
            
            osm: null, // OpenStreetMap base layer
            osmHOT: null, // OpenStreetMap Humanitarian base layer
            
            clusterGroup: null, // Marker cluster group for grouping markers
            layerControl: null, // Layer control for toggling base and overlay layers
            
            // GeoJSON layers
            lineaJson: null, // GeoJSON layer for "Linea de seguridad"
            cotaJson: null, // GeoJSON layer for "Cota de inundacion"
            iquiqueJson: null, // GeoJSON layer for "Iquique"

            init() {                
                // Initialize the map with default center and zoom level
                const zoom = 14;
                const center = [-20.216700, -70.14222];
                this.osm = new L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }); 
                this.osmHOT = new L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap contributors, Tiles style by Humanitarian OpenStreetMap Team hosted by OpenStreetMap France' });
                this.map = new L.map(this.$el, { center: center, zoom: zoom, layers: [this.osm] });
                this.clusterGroup = L.markerClusterGroup().addTo(this.map);
                this.baseLayers = {"OSM": this.osm, "OSM HOT": this.osmHOT};
                this.layerControl = new L.Control.Layers(this.baseLayers).addTo(this.map);
                this.updateMarkers(markers);

                // Listen for marker updates and refresh the map
                window.addEventListener('markersUpdated', event => {
                    const newMarkers = event.detail.markers;
                    this.updateMarkers(newMarkers);
                    if (newMarkers.length) {
                        const { lat, lng } = newMarkers[0];
                        this.map.setView([lat, lng], zoom);
                    }
                });

                // Load GeoJSON layers from the server
                Promise.all([
                    fetch(baseUrl + "/json/linea_seguridad_iquique.geojson").then(res => res.json()),
                    fetch(baseUrl + "/json/cota_30_tarapaca.geojson").then(res => res.json()),
                    fetch(baseUrl + "/json/2012_iquique.geojson").then(res => res.json()),
                    fetch(baseUrl + "/json/UTF-81_Aluvion.geojson").then(res => res.json())
                ]).then(([lineaJson, cotaJson, iquiqueJson, aluvionJson]) => {
                    // Initialize GeoJSON layers with styles
                    this.lineaJson = new L.GeoJSON(lineaJson, { style: { color: '#00FF00' } });
                    this.cotaJson = new L.GeoJSON(cotaJson, { style: { color: '#FF0000' } });
                    this.iquiqueJson = new L.GeoJSON(iquiqueJson, { style: { color: '#0000FF' } });
                    this.aluvionJson = new L.GeoJSON(aluvionJson, { style: { color: '#660066' } });                                        

                    // Add overlay layers to the layer control
                    this.layerControl.addOverlay(new L.LayerGroup([this.lineaJson]), 'Linea de seguridad')
                    .addOverlay(new L.LayerGroup([this.cotaJson]), 'Cota de inundacion')
                    .addOverlay(new L.LayerGroup([this.iquiqueJson]), 'Iquique')
                    .addOverlay(new L.LayerGroup([this.aluvionJson]), 'Zona de aluvion');
                });
            },

            // Update the markers on the map
            async updateMarkers(data) {
                this.clusterGroup.clearLayers();
                await data.forEach(d => {                    
                    const marker = L.marker(
                        [
                            d.lat,
                            d.lng
                        ]
                    );
                    // Attach additional data to the marker
                    marker.lat = d.lat;
                    marker.lng = d.lng;
                    marker.name = d.name;
                    marker.address = d.address;
                    marker.url = d.url;
                    marker.flooded = d.flooded;
                    marker.alluvion = d.alluvion;
                    marker.bindPopup("Cargando...");
                    marker.on('click', this.markerOnClick.bind(this));
                    this.clusterGroup.addLayer(marker);
                });
            },

            // Handle marker click event and update popup content
            markerOnClick(e){
                let content = `<a href="${e.target.url}" target="_blank">${e.target.name}</a><br>${e.target.address}`;
                content += e.target.flooded ? '<br> En zona de inundacion' : '';
                content += e.target.alluvion ? '<br> En zona de aluvion' : '';
                e.target.getPopup().setContent(content);
            }
        }));
    });
    </script>
@endpush