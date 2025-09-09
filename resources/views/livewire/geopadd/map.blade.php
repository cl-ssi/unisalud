<div>
    @stack('styles')
    <style>
        #map {
            width: 100%;
            height: 94vh;
        }
    </style>
    <div id="map"></div>
    <div
        class="hidden"
        id="dev-data"
        data-conditions="{{json_encode($conditions_id)}}"
        data-organizations="{{ json_encode($organizations_id)}}"
        data-users="{{ json_encode($users_id)}}"
        data-risks="{{json_encode($risks)}}">
        data-conditions="{{json_encode($conditions_id)}}"
        data-organizations="{{ json_encode($organizations_id)}}"
        data-users="{{ json_encode($users_id)}}"
        data-risks="{{json_encode($risks)}}">
    </div>
    @stack('scripts')

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/4.0.0/Control.FullScreen.min.css" integrity="sha512-Dww58CIezLb4kFGtZ8Zlr85kRDwJgyPhe3gVABsvnJruZuYn3xCTpLbE3iBT5hGbrfCytJnZ4aiI3MxN0p+NVQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" integrity="sha256-YU3qCpj/P06tdPBJGPax0bm6Q1wltfwjsho5TR4+TYc=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" integrity="sha256-YSWCMtmNZNwqex4CEw1nQhvFub2lmU7vcCKP+XVwwXA=" crossorigin="anonymous">
    @endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/4.0.0/Control.FullScreen.min.js" integrity="sha512-javanlE101qSyZ7XdaJMpB/RnKP4S/8jq1we4sy50BfBgXlcVbIJ5LIOyVa2qqnD+aGiD7J6TQ4bYKnL1Yqp5g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="{{asset('/js/geopadd/map.js')}}"></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            const el = document.getElementById('dev-data');
            let conditions = JSON.parse(el.dataset.conditions);
            let organizations = JSON.parse(el.dataset.organizations);
            let users = JSON.parse(el.dataset.users);
            let risks = JSON.parse(el.dataset.risks);
            Livewire.on('dataUpdated', (event) => {
                let data = event[0].data;
                setData(data);
            });

            function setData(data) {
                conditions = data.conditions_id;
                organizations = data.organizations_id;
                users = data.users_id;
                risks = data.risks;
                console.log(risks);
            }
        });
    </script>
    @endpush
</div>