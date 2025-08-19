<x-filament::widget>
    <x-filament::section>
        <div
            wire:ignore
            x-data="mapComponent(@js($markers), @js($baseUrl))"
            style="height: 500px; z-index: 1;"></div>
    </x-filament::section>
</x-filament::widget>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/4.0.0/Control.FullScreen.min.css" integrity="sha512-Dww58CIezLb4kFGtZ8Zlr85kRDwJgyPhe3gVABsvnJruZuYn3xCTpLbE3iBT5hGbrfCytJnZ4aiI3MxN0p+NVQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" integrity="sha256-YU3qCpj/P06tdPBJGPax0bm6Q1wltfwjsho5TR4+TYc=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" integrity="sha256-YSWCMtmNZNwqex4CEw1nQhvFub2lmU7vcCKP+XVwwXA=" crossorigin="anonymous">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Turf.js/6.5.0/turf.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/4.0.0/Control.FullScreen.min.js" integrity="sha512-javanlE101qSyZ7XdaJMpB/RnKP4S/8jq1we4sy50BfBgXlcVbIJ5LIOyVa2qqnD+aGiD7J6TQ4bYKnL1Yqp5g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
            aluvionJson: null, // GeoJSON layer for "UTF-81_Aluvion"


            init() {
                Promise.all([
                    fetch(baseUrl + "/json/linea_seguridad_iquique.geojson").then(res => res.json()),
                    fetch(baseUrl + "/json/cota_30_tarapaca.geojson").then(res => res.json()),
                    fetch(baseUrl + "/json/UTF-81_Aluvion.geojson").then(res => res.json())
                ]).then(([lineaJson, cotaJson, aluvionJson]) => {
                    this.osm = new L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    });
                    this.map = new L.map(this.$el, {
                        center: [-20.216700, -70.14222],
                        zoom: 14,
                        zoomAnimation: true,
                        layers: [this.osm],
                        fullscreenControl: true,
                        renderer: L.canvas()
                    });
                    this.clusterGroup = L.markerClusterGroup();
                    this.updateMarkers(markers);

                    // Listen for marker updates and refresh the map
                    Livewire.on('markersUpdated', (markers) => {
                        const newMarkers = JSON.parse(markers);
                        this.updateMarkers(newMarkers);
                        if (newMarkers.length) {
                            const {
                                lat,
                                lng
                            } = newMarkers[0];
                            // this.map.setView([lat, lng], zoom);
                        }
                    });

                    // Load GeoJSON layers from the server

                    this.lineaJson = new L.GeoJSON(lineaJson, {
                        style: {
                            color: '#00FF00'
                        }
                    });
                    this.cotaJson = new L.GeoJSON(cotaJson, {
                        style: {
                            color: '#FF0000'
                        }
                    });
                    this.aluvionJson = new L.GeoJSON(aluvionJson, {
                        style: {
                            color: '#660066'
                        }
                    });
                    this.layerControl = new L.Control.Layers(this.baseLayers, {
                        'Linea de seguridad': new L.LayerGroup([this.lineaJson]),
                        'Cota de inundacion': new L.LayerGroup([this.cotaJson]),
                        'Zona de aluvion': new L.LayerGroup([this.aluvionJson]),
                        'Marcadores': this.clusterGroup,
                    }).addTo(this.map);
                });
            },

            // Update the markers on the map
            async updateMarkers(data) {
                console.log(data);
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
            markerOnClick(e) {
                let content = `<a href="${e.target.url}" target="_blank">${e.target.name}</a><br>${e.target.address}`;
                content += e.target.flooded ? '<br> En zona de inundacion' : '';
                content += e.target.alluvium ? '<br> En zona de aluvion' : '';
                e.target.getPopup().setContent(content);
            },
        }));
    });
</script>
@endpush