@extends('layouts.app')

@section('content')

<h4 class="mb-3">Listado de correos electrónicos del encargado de Epidemiología del establecimiento</h4>

<p>
    En esta sección se encuentran los correos electrónicos de los encargados de epidemiología para cada organización a la que el usuario tiene acceso. Si hay más de un encargado, se deben separar por ",". El encargado de epidemiología recibirá un correo en caso de encontrar un caso en proceso de chagas.
</p>

@foreach($organizations as $organization)
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Correo electrónico actual de encargado de epidemiologia de {{ $organization->alias?? '' }}</h5>
        <p class="card-text">{{ $organization->epi_mail ??'' }}</p>
    </div>
</div>
@endforeach

<h5 class="mb-3">Actualizar correo electrónico</h5>

<form method="POST" action="{{ route('epi.updateMail', $organization) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="for_epi_email">Nuevo correo electrónico</label>
        <input type="email" class="form-control" id="epi_mail" name="epi_mail" autocomplete="off" required>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>

@endsection