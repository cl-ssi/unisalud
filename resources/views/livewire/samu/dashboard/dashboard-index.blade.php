<div>
    @include('samu.nav')

    <h3 class="mb-2">
        <i class="fas fa-chart-line"></i> Panel de Estadísticas
    </h3>
   
    <div class="form-row mb-2">
        <div class="col">
            <div class="card" style="height:350px">
                <h6 class="card-header"># de Eventos atendidos en los últimos 30 días</h6>
                <div class="card-body pt-2">
                    {!! $eventLastMonth->container() !!}
                    {!! $eventLastMonth->script() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-12 col-md-6">
            <div class="card" style="height:300px">
                <h6 class="card-header"># de Eventos atendidos por comuna durante el mes de {{ translateMonth(now()->format('F')) }}</h6>
                <div class="card-body pt-2">
                    {!! $eventByCommune->container() !!}
                    {!! $eventByCommune->script() !!}
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card" style="height:300px">
                <h6 class="card-header"># de Eventos atendidos por móviles durante el mes de {{ translateMonth(now()->format('F')) }}</h6>
                <div class="card-body pt-2">
                    {!! $eventByMobile->container() !!}
                    {!! $eventByMobile->script() !!}
                </div>
            </div>  
        </div>
    </div>
    
    <div class="form-row mb-2">
        <div class="col-12 col-md-6">
            <div class="card" style="height:300px">
                <h6 class="card-header"># de Eventos atendidos agrupados por sexo durante el mes de {{ translateMonth(now()->format('F')) }}</h6>
                <div class="card-body pt-2">
                    {!! $eventBySex->container() !!}
                    {!! $eventBySex->script() !!}
                </div>
            </div>  
        </div>
        <div class="col-12 col-md-6">
            <div class="card" style="height:300px">
                <h6 class="card-header"># de Eventos atendidos en los últimos 6 meses</h6>
                <div class="card-body pt-2">
                    {!! $eventByMonth->container() !!}
                    {!! $eventByMonth->script() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="form-row">
        @livewire('samu.stadistic')
    </div>
</div>

@section('custom_js')
    <!-- Charting library -->
    <script src="https://unpkg.com/chart.js@^2.9.3/dist/Chart.min.js"></script>
    <!-- Chartisan -->
    <script src="https://unpkg.com/@chartisan/chartjs@^2.1.0/dist/chartisan_chartjs.umd.js"></script>
@endsection
