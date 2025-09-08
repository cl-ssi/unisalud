const LATITUDE_MAP = -20.249956245793552;
const LONGITUDE_MAP = -70.12817358465354;
let urlGeoPadds = '/api/geopadds';
let markers = [];


let layer = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
// map.addLayer(layer);

const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
});

const satelliteLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
    maxZoom: 20,
    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
});
let baseMaps = {
    "OpenStreetMap": osmLayer,
    "Satellite": satelliteLayer
};

let mapOptions = {
    center: [LATITUDE_MAP, LONGITUDE_MAP],
    zoom: 14,
    layers: [osmLayer, satelliteLayer],
    fullscreenControl: true,
};
let map = new L.map('map', mapOptions);

getOverlays(map).then(overlays => {
    new L.Control.Layers(baseMaps, overlays, { collapsed: false }).addTo(map);
})


async function getOverlays(map) {
    try {
        const [lineaJson, cotaJson, aluvionJson] = await Promise.all([
            fetch('/json/linea_seguridad_iquique.geojson').then(res => res.json()),
            fetch('/json/cota_30_tarapaca.geojson').then(res => res.json()),
            fetch('/json/UTF-81_Aluvion.geojson').then(res => res.json())
        ]);
        let overlays = {
            'lineaJson': new L.GeoJSON(lineaJson, {
                style: {
                    color: '#00FF00',
                    weight: 2,
                    opacity: 0.7
                }
            }).addTo(map),

            'cotaJson': new L.GeoJSON(cotaJson, {
                style: {
                    color: '#FF0000',
                    weight: 2,
                    opacity: 0.7
                }
            }).addTo(map),

            'aluvionJson': new L.GeoJSON(aluvionJson, {
                style: {
                    color: '#660066',
                    weight: 2,
                    opacity: 0.7
                }
            }).addTo(map),
        };
        return overlays;


    } catch (error) {
        console.error(error);
    }
}

async function addMarkers() {
    try {
        const response = await axios.get(urlGeoPadds);
        geoPadds = response.data.geoPadds;

        geoPadds.map((geoPadd) => {
            if (geoPadd.latitude != null && geoPadd.longitude != null) {
                // let icon = new L.icon(iconGeoPadd);
                let latLng = [geoPadd.latitude, geoPadd.longitude];
                // let marker = new L.marker(latLng, { icon: icon });
                let marker = new L.marker(latLng);
                marker.bindTooltip(`${geoPadd.name}`).openTooltip();
                marker.addTo(map);
            }
        });
    } catch (error) {
        console.error(error);
    }
}

function deleteMarkers() {
    markers.map((markers) => {
        map.removeLayer(markers);
    });
}



setInterval(() => {

    // markers.refreshClusters();
    deleteMarkers();
    addMarkers(map)
}, 20000);