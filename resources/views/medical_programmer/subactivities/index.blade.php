@extends('layouts.app')

@section('content')

<h3 class="mb-3">Listado de Subactividades</h3>

<a class="btn btn-primary mb-3" href="{{ route('medical_programmer.subactivities.create') }}">
    <i class="fas fa-plus"></i> Agregar nueva
</a>

<table class="table table-sm table-borderer">
    <thead>
        <tr>
            <th>Especialidad</th>
            <th>Actividad</th>
            <th>SubActividad Abrev.</th>
            <th>SubActividad</th>
            <th>Rendimiento</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach( $subactivities as $subactivity )
        <tr>
            <td>@if($subactivity->specialty){{ $subactivity->specialty->specialty_name }}@endif</td>
            <td>@if($subactivity->activity){{ $subactivity->activity->activity_name }}@endif</td>
            <td>{{ $subactivity->sub_activity_abbreviated }}</td>
            <td>{{ $subactivity->sub_activity_name }}</td>
            <td>{{$subactivity->performance}}</td>
            <td>
      				<a href="{{ route('medical_programmer.subactivities.edit', $subactivity) }}"
      					class="btn btn-sm btn-outline-secondary">
      					<span class="fas fa-edit" aria-hidden="true"></span>
      				</a>
      				<form method="POST" action="{{ route('medical_programmer.subactivities.destroy', $subactivity) }}" class="d-inline">
      					@csrf
      					@method('DELETE')
      					<button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('¿Está seguro de eliminar la información?');">
      						<span class="fas fa-trash-alt" aria-hidden="true"></span>
      					</button>
      				</form>
      			</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection

@section('custom_js')

@endsection
