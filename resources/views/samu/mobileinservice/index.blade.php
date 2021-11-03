@extends('layouts.app')

@section('content')

@include('nav')


<h3 class="mb-3"><i class="fas fa-ambulance"></i> Listado de Moviles - Tripulación</h3>



<div class="card mb-3">
        <div class="card-body">
            
                <div class="form-row mb-3 ml-2">
                    <div class="col-12 col-md-8">
                        <form method="GET" class="form-horizontal" action="">
                            <div class="input-group mb-sm-0">
                                <input class="form-control" type="text" name="search" autocomplete="off" id="for_search"
                                    style="text-transform: uppercase;"
                                    placeholder="MOVIL" value="" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                        
                    <div class="col-12 col-md-4">
                        <a class="btn btn-success" href="{{ route('samu.mobileinservice.create') }}">
                        <i class="fas fa-ambulance"> <i class="fas fa-plus"></i> </i> Agregar Moviles en turno
                        </a>
                    </div>
                
                </div> 
            

            <div class="table-responsive col-md-12 mb-3 ">
            @foreach($shifts as $shift)
            @foreach($shift->mobilesInService as $mobil)
                    <table class="table table-sm table-bordered table-striped small">
                  
                  
                        <thead>
                       
                            <tr class="text-center table-info">
                              
                              <th colspan="5"><b>DETALLES DE MOVILES DE TURNO</b></th>
                            </tr>
                            
                            <tr class="text-center table-success">
                              
                              <th colspan="5"><b>Turno: {{ $shift->id }} - {{ $shift->date }} {{ $shift->type }} {{ $shift->opening_time }}</b></th>
                            </tr>
                        
                            <tr class="text-center table-secondary">
                        
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Placa</th>
                                <th>Tipo</th>
                                <th>Editar</th>

                            </tr>
                        </thead>
                        <tbody>
              
                            <tr>
                                <td>{{ $mobil->code }}</td>
                                <td>{{ $mobil->name }} </td>
                                <td>{{ $mobil->type}} </td>                             
                                <td class="bg-success text-center text-white">Activo</td>
                                <td><a href="{{ route('samu.mobileinservice.edit',1) }}">Editar</a> </td>
                                @livewire('samu.mobile-crew',['pivot' => $mobil->pivot])   
                            </tr>
                            
                        </tbody>
                     
                    
                    </table>
                    @endforeach  
                    @endforeach
                </div>
            </div>
         

            <hr>
        
        </div>
</div>


@endsection
@section('custom_js')
<script>
$(function () {
        $('[data-toggle="popover" ]').popover()
    })
</script>
@endsection