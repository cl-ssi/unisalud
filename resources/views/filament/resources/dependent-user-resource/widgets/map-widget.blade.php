<x-filament::widget>
    <x-filament::section>
        <div id="map"></div>
        <div
            class="hidden"
            id="dev-data"
            data-markers="{{json_encode($markers)}}"
            data-baseurl="{{json_encode($baseUrl)}}">
        </div>
    </x-filament::section>
</x-filament::widget>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/4.0.0/Control.FullScreen.min.css" integrity="sha512-Dww58CIezLb4kFGtZ8Zlr85kRDwJgyPhe3gVABsvnJruZuYn3xCTpLbE3iBT5hGbrfCytJnZ4aiI3MxN0p+NVQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    #map {
        height: 500px;
        width: 100%;
        z-index: 1;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/4.0.0/Control.FullScreen.min.js" integrity="sha512-javanlE101qSyZ7XdaJMpB/RnKP4S/8jq1we4sy50BfBgXlcVbIJ5LIOyVa2qqnD+aGiD7J6TQ4bYKnL1Yqp5g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    document.addEventListener('livewire:initialized', function() {
        let lineaData = null;
        let cotaData = null;
        let aluvionData = null;
        let mapRef = null;
        let markers = null;
        let markersLayerRef = null;
        let osmLayerRef = null;
        let geoJsonLayersRef = {};
        let layerControlRef = null;
        let mapContainerId = 'map';
        Promise.all([
            fetch("/json/linea_seguridad_iquique.geojson").then(res => res.json()),
            fetch("/json/cota_30_tarapaca.geojson").then(res => res.json()),
            fetch("/json/UTF-81_Aluvion.geojson").then(res => res.json())
        ]).then(([lineaData, cotaData, aluvionData]) => {
            lineaData = lineaData;
            cotaData = cotaData;
            aluvionData = aluvionData;
            setupMap()
        }).catch(error => {
            console.error('Error al cargar o procesar los datos GeoJSON:', error);
        });

        function setupMap() {
            console.log('Inicializando/Reinicializando el mapa...');
            const mapElement = document.getElementById(mapContainerId);
            const dataElement = document.getElementById('dev-data');

            if (!mapElement) {
                console.error(`Elemento con ID '${mapContainerId}' no encontrado.`);
                return;
            }
            if (!dataElement) {
                console.error('Elemento con ID "dev-data" no encontrado.');
                return;
            } else {
                markers = JSON.parse(dataElement.dataset.markers);
                console.log('datos:', markers);
            }

            // Si ya existe un mapa, destrúyelo primero
            if (mapRef) {
                console.log('Destruyendo mapa existente...');
                try {
                    mapRef.remove();
                } catch (e) {
                    console.warn('Error al destruir el mapa existente:', e);
                }
                mapRef = null;
                markersLayerRef = null;
                osmLayerRef = null;
                geoJsonLayersRef = {};
            }

            // Verificar si el contenedor es visible y tiene tamaño
            const rect = mapElement.getBoundingClientRect();
            const isVisible = rect.width > 0 && rect.height > 0;


            if (!isVisible) {
                console.warn('El contenedor del mapa no es visible o tiene tamaño 0. Retrasando inicialización.');
                // Si no es visible, retrasar la inicialización
                setTimeout(setupMap, 100); // Reintentar en 100ms
                return;
            }

            // Crear capa base
            osmLayerRef = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            });

            // Crear el mapa
            mapRef = L.map(mapContainerId, {
                center: [-20.216700, -70.14222],
                zoom: 14,
                zoomAnimation: true,
                layers: [osmLayerRef],
                fullscreenControl: true,
                renderer: L.canvas()
            });

            // Inicializar capa de marcadores
            markersLayerRef = L.layerGroup().addTo(mapRef);

            // Cargar y agregar capas GeoJSON

            if (!mapRef) return;

            geoJsonLayersRef.linea = L.geoJSON(lineaData, {
                style: {
                    color: '#00FF00',
                    weight: 2,
                    opacity: 0.7
                }
            }).addTo(mapRef);

            geoJsonLayersRef.cota = L.geoJSON(cotaData, {
                style: {
                    color: '#FF0000',
                    weight: 2,
                    opacity: 0.7
                }
            }).addTo(mapRef);

            geoJsonLayersRef.aluvion = L.geoJSON(aluvionData, {
                style: {
                    color: '#660066',
                    weight: 2,
                    opacity: 0.7
                }
            }).addTo(mapRef);

            const baseLayers = {
                'OpenStreetMap': osmLayerRef
            };
            const overlayLayers = {
                'Línea de seguridad': geoJsonLayersRef.linea,
                'Cota de inundación': geoJsonLayersRef.cota,
                'Zona de aluvión': geoJsonLayersRef.aluvion,
                'Marcadores': markersLayerRef
            };

            layerControlRef = L.control.layers(baseLayers, overlayLayers, {
                collapsed: false
            }).addTo(mapRef);

            updateMarkers(markers);

            // Forzar invalidación del tamaño después de un breve retraso
            // para asegurar que el contenedor ha terminado de renderizarse
            setTimeout(() => {
                if (mapRef) {
                    console.log('Forzando invalidación del tamaño del mapa...');
                    mapRef.invalidateSize();
                }
            }, 80); // 100ms después de cargar todo

        }


        function updateMarkers(markers) {
            console.log('Actualizando marcadores...');
            if (!mapRef || !markersLayerRef) {
                console.warn('Mapa o capa de marcadores no inicializados.');
                return;
            }

            try {
                markersLayerRef.clearLayers();

                if (!markers || !markers.length) {
                    console.log('No hay marcadores para mostrar.');
                    console.log(markers);
                    return;
                }

                const newMarkers = [];
                markers.forEach(d => {
                    const marker = L.marker([parseFloat(d.lat), parseFloat(d.lng)]);

                    let popupContent = `<div class="popup-content">
                    <h4><a href="${d.url || '#'}" target="_blank">${d.name || 'Sin nombre'}</a></h4>
                    <p>${d.address || 'Sin dirección'}</p>`;
                    if (d.flooded) {
                        popupContent += '<p style="color:red;">En zona de inundación</p>';
                    }
                    if (d.alluvion) {
                        popupContent += '<p style="color:purple;">En zona de aluvión</p>';
                    }
                    popupContent += '</div>';

                    marker.bindPopup(popupContent);
                    newMarkers.push(marker);
                });

                if (newMarkers.length > 0) {
                    newMarkers.forEach(m => {
                        m.addTo(markersLayerRef);
                    })
                    console.log(`Añadidos ${newMarkers.length} nuevos marcadores.`);
                }

                // Opcional: Ajustar la vista a los marcadores
                // if (newMarkers.length > 0) {
                //     const group = new L.featureGroup(newMarkers);
                //     mapRef.fitBounds(group.getBounds().pad(0.1));
                // }

            } catch (e) {
                console.error('Error al actualizar marcadores:', e);
            }
        }

        // Escuchar eventos de Livewire
        Livewire.on('markersUpdated', (event) => {
            console.log('Evento markersUpdated recibido.');
            // Retrasar la reinicialización para dar tiempo a que Livewire
            // termine de actualizar el DOM
            setTimeout(setupMap, 50); // 50ms puede ser suficiente
        });
    });
</script>
@endpush