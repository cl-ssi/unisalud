<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    </form>
    {{--
    @if($conditionType != NULL)
    --}}
        <div id="map" style="height: 500px; width: 100%;"></div>

        <script>
            function initMap() {
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: { 
                        lat: -34.397, 
                        lng: 150.644 },
                    zoom: 8
                });

                var users = @json($users);

                users.forEach(function(user) {
                    if (user.latitude && user.longitude) {
                        new google.maps.Marker({
                            position: { 
                                lat: parseFloat(user.latitude), 
                                lng: parseFloat(user.longitude) 
                            },
                            map: map
                        });
                    }
                });
            }
        </script>

        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC9p3-3qnUpJn10NVGd80AQFCMrq5TLSyQ&callback=initMap"></script>
    {{--
    @endif
    --}}
</x-filament::page>