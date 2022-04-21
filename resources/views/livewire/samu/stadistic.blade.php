<div>
    <h3 class="mt-4">Eventos por variables</h3>

    <div class="form-row">
        <fieldset class="form-group col-md-2">
            <label for="for-from">Desde</label>
            <input type="date" class="form-control" wire:model="from">
            @error('from') <span class="text-danger">{{ $message }}</span> @enderror
        </fieldset>

        <fieldset class="form-group col-md-2">
            <label for="for-to">Hasta</label>
            <input type="date" class="form-control" wire:model="to">
            @error('to') <span class="text-danger">{{ $message }}</span> @enderror
        </fieldset>

        <fieldset class="form-group col-md-3">
            <label for="for-key">Clave</label>
            <select class="form-control" wire:model="key_id" id="for-key">
                <option value="">Selecciona una Clave</option>
                @foreach($keys as $key)
                <option value="{{ $key->id }}">       
                    {{ $key->key }}  - {{ $key->name }}
                </option>
                @endforeach
            </select>
            @error('code') <span class="text-danger">{{ $message }}</span> @enderror
        </fieldset>

        <fieldset class="form-group col-md-3">
            <label for="for-key">Clave retorno</label>
            <select class="form-control" wire:model="return_key_id" id="for-return_key_id">
                <option value="">Selecciona una Clave</option>
                @foreach($keys as $key)
                <option value="{{ $key->id }}">       
                    {{ $key->key }}  - {{ $key->name }}
                </option>
                @endforeach
            </select>
            @error('code') <span class="text-danger">{{ $message }}</span> @enderror
        </fieldset>

        {{--
        <fieldset class="form-group col-12 col-md-2">
            <label for="for-commune">Comuna</label>
            <select class="form-control" wire:model="commune_id" id="for-commune">
                <option value="">Todas las comunas</option>
                @foreach($communes as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            @error('code') <span class="text-danger">{{ $message }}</span> @enderror
        </fieldset>
        --}}

    </div>
    
    <button class="btn btn-primary" wire:click="search">Buscar</button>

    <h4 class="text-center">Total de eventos {{ $total }}</h4>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <div id="piechart" style="width: 900px; height: 500px;"></div>

    <script>
        google.charts.load('current', {'packages':['corechart']});

        window.livewire.on('re-render', data => {
            var data = google.visualization.arrayToDataTable(data);
            var options = {
                title: 'Eventos'
            };
            var chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
        })
    </script>
</div>