@extends('layouts.app')

@section('content')

@include('samu.nav')

<h3 class="mb-3"><i class="fas fa-ambulance"></i> Listado de Móviles
    <a class="btn btn-success float-right" href="{{ route('samu.mobile.create') }}">
        <i class="fas fa-plus"></i> Agregar
    </a>
</h3>

<div class="row mb-4">
    <div class="col-12 col-md-6">
        <form method="GET" class="form-horizontal" action="{{ route('samu.mobile.index') }}">
            <div class="input-group mb-sm-0">
                <input class="form-control" type="text" name="search_mobile" autocomplete="off" id="for_search"
                    style="text-transform: uppercase;"
                    placeholder="Nombre Codigo" value="">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-striped">
        <thead>
            <tr class="table-primary">
                <th></th>
                <th>Codigo</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Patente</th>
                <th>Descripción</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($mobiles as $mobile)
            <tr>
                <td>
                    <a href="{{ route('samu.mobile.edit', $mobile) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                </td>
                <td>{{ $mobile->code }}</td>
                <td>{{ $mobile->name }}</td>
                <td>{{ optional($mobile->type)->name }}</td>
                <td>{{ $mobile->status ? 'Activo':'Inactivo' }}</td>
                <td>{{ $mobile->plate }}</td>
                <td>{{ $mobile->description }}</td>
                <td>
                    <form method="POST" action="{{ route('samu.mobile.destroy', $mobile) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
