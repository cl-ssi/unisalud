@extends('layouts.document')

@section('title', 'Reporte de Lista de Espera')

@section('linea1', 'Servicio de Salud Tarapacá')
@section('linea2', 'Subdirección de Gestión Asistencial')
@section('linea3', 'Fecha de generación: ' . now()->format('d-m-Y'))

@section('content')
    <div style="width: 49%; display: inline-block;">
        <div class="siete">
            
        </div>
    </div>

    <div style="clear: both; padding-bottom: 150px">&nbsp;</div>

    <div class="siete">
        <h1 align="center">Reporte de Lista de Espera</h1>
        <table class="tabla center">
            <thead>
                <tr>
                    <th>Tópico</th>
                    <th>Número</th>
                </tr>
            </thead>
            <tbody>
                <!-- NO DERIVADO - DERIVADO - CITADO - ATENDIDO - INASISTENTE - INCONTACTABLE - EGRESADO -->
                <tr>
                    <td>Usuarios</td>
                    <td>{{ $waitlistResume['users'] }}</td>
                </tr>
                {{--
                <tr>
                    <td>Pendientes</td>
                    <td>{{ $waitlistResume['pendientes'] }}</td>
                </tr>
                --}}
                <tr>
                    <td>Usuarios Derivados</td>
                    <td>{{ $waitlistResume['derivado'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Usuarios Contactados</td>
                    <td>{{ $waitlistResume['contactados'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Usuarios No Contactados</td>
                    <td>{{ $waitlistResume['no contactados'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Usuarios Rechazan Atención</td>
                    <td>{{ $waitlistResume['rechazos egresados'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Usuarios Inasistentes</td>
                    <td>{{ $waitlistResume['inasistentes'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>F.O. Realizados (Total y Mensual)</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection