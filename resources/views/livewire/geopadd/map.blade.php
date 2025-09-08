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
        data-conditions="{{ htmlspecialchars(json_encode($conditions_id), ENT_QUOTES, 'UTF-8') }}"
        data-organizations="{{ htmlspecialchars(json_encode($organizations_id), ENT_QUOTES, 'UTF-8') }}"
        data-users="{{ htmlspecialchars(json_encode($users_id), ENT_QUOTES, 'UTF-8') }}"
        data-risks="{{ htmlspecialchars(json_encode($risks), ENT_QUOTES, 'UTF-8') }}">
    </div>
    @stack('scripts')

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/4.0.0/Control.FullScreen.min.css" integrity="sha512-Dww58CIezLb4kFGtZ8Zlr85kRDwJgyPhe3gVABsvnJruZuYn3xCTpLbE3iBT5hGbrfCytJnZ4aiI3MxN0p+NVQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" integrity="sha256-YU3qCpj/P06tdPBJGPax0bm6Q1wltfwjsho5TR4+TYc=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" integrity="sha256-YSWCMtmNZNwqex4CEw1nQhvFub2lmU7vcCKP+XVwwXA=" crossorigin="anonymous">
    @endpush
    @script
    <script>
        document.addEventListener('livewire:load', function() {
            const el = document.getElementById('dev-data');
            let conditions = JSON.parse(el.dataset.conditions);
            let organizations = JSON.parse(el.dataset.organizations);
            let users = JSON.parse(el.dataset.users);
            let risks = JSON.parse(el.dataset.risks);
            console.log(risks);
            document.addEventListener('dataUpdated', (event) => {
                setData(el);
                console.log(risks);
            });
        });

        function setData(el) {
            conditions = JSON.parse(el.dataset.conditions);
            organizations = JSON.parse(el.dataset.organizations);
            users = JSON.parse(el.dataset.users);
            risks = JSON.parse(el.dataset.risks);
        }

        // let conditions_id = @this.get('conditions_id');
        // let organizations_id = @this.get('organizations_id');
        // let users_id = @this.get('users_id');
        // let risks = @this.get('risks');
        // document.addEventListener('dataUpdated', (e, d) => {

        // });
    </script>
    @endscript
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.fullscreen/4.0.0/Control.FullScreen.min.js" integrity="sha512-javanlE101qSyZ7XdaJMpB/RnKP4S/8jq1we4sy50BfBgXlcVbIJ5LIOyVa2qqnD+aGiD7J6TQ4bYKnL1Yqp5g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <!-- <script src="{{asset('/js/geopadd/map.js')}}"></script> -->
    @endpush
</div>