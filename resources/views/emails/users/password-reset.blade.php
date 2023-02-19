@extends('layouts.mail')

@section('content')

<div style="text-align: justify;">

    <h4>Estimado/a usuario: </h4>

    <p>Su nueva clave de acceso local a UniSalud y NeoSalud es:</p>
    <strong>{{ $newPassword }}</strong>

    <br>
    <br>
    <p>
        Atentamente
    </p>

</div>
@endsection


@section('firmante')
    UniSalud / NeoSalud
@endsection

@section('linea1')
    Departamento de Tecnologías de la Información y Comunicaciones
@endsection