@extends('layouts.app')

@section('content')

<h3 class="mb-3">Listado de RRHH</h3>

<a class="btn btn-primary mb-3" href="{{ route('medical_programmer.rrhh.create') }}">
    <i class="fas fa-plus"></i> Agregar nuevo
</a>

<table class="table table-sm table-borderer table-responsive-xl">
    <thead>
        <tr>
            <th>RUT</th>
            <th>DV</th>
            <th>Nombre</th>
            <!-- <th>Apellido Paterno</th>
            <th>Apellido Materno</th> -->
            <th>Función</th>
            <th>Prof/Espec</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach( $rrhh as $user)
        <tr>
            <td>{{ $user->IdentifierRun->value }}</td>
            <td>{{ $user->IdentifierRun->dv }}</td>
            <td>{{ $user->OfficialFullName }}</td>
            <!-- <td>{{ $user->fathers_family }}</td>
            <td>{{ $user->mothers_family }}</td> -->
            <td>{{ $user->job_title }}</td>
            <td nowrap>
                @foreach ($user->specialties as $key => $specialty)
                    {{$specialty->specialty_name}},
                    @if ($key == 2)
                        @break
                    @endif
                @endforeach
                @foreach ($user->professions as $key => $profession)
                    {{$profession->profession_name}},
                    @if ($key == 2)
                        @break
                    @endif
                @endforeach
            </td>
            <td>
      				<a href="{{ route('medical_programmer.rrhh.edit', $user) }}"
      					class="btn btn-sm btn-outline-secondary">
      					<span class="fas fa-edit" aria-hidden="true"></span>
      				</a>
      				<form method="POST" action="{{ route('medical_programmer.rrhh.destroy', $user) }}" class="d-inline">
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

{{ $rrhh->links('pagination::bootstrap-4') }}

@endsection

@section('custom_js')

@endsection
