@extends('layouts.app')

@section('content')
<h4 class="mb-3">Listado de Solicitudes de Examenes de Chagas de {{$tray}}</h4>

<div class="table-responsive">
    <table class="table table-sm table-bordered" id="tabla_casos">
        <thead>
            <tr>
                <th nowrap>ID</th>
                <th>Fecha muestra</th>
                <th>Origen</th>
                <th>Nombre</th>
                <th>RUN o Identificación</th>
                <th>Edad</th>
                <th>Sexo</th>
                <th>Nacionalidad</th>
                <th>Fecha de Resultado Tamizaje</th>
                <th>Resultado Tamizaje</th>
                <th>Fecha de Resultado Confirmación</th>
                <th>Resultado Confirmación</th>
                <th>Observación</th>
                <th>Descargar resultado (Bosquejo)</th>
            </tr>
        </thead>



        <tbody id="tableCases">
            @foreach($suspectcases as $suspectcase)
            <tr>
                <td>{{$suspectcase->id??''}}
                    @can('Epi: Add Value')
                    <a href="{{ route('epi.chagas.edit',$suspectcase) }}" pclass="btn_edit"><i class="fas fa-edit"></i></a>
                    @endcan
                </td>
                <td>{{$suspectcase->sample_at? $suspectcase->sample_at: ''}}</td>
                <td>{{$suspectcase->organization->alias??''}}</td>
                <td>{{$suspectcase->patient->OfficialFullName ??''}}</td>
                <td>@if($suspectcase->patient->identifierRun)
                    {{$suspectcase->patient->identifierRun->value ??''}}-{{$suspectcase->patient->identifierRun->dv}}
                    @else
                    {{ $suspectcase->patient->Identification->value ??''}}
                    @endif
                </td>
                <td>
                    {{\Carbon\Carbon::parse($suspectcase->patient->birthday)->age}}
                </td>
                <td>{{$suspectcase->patient->actualSex()->text ??''}}</td>
                <td>{{$suspectcase->patient->nationality->name ??''}}</td>
                <td>{{$suspectcase->chagas_result_screening_at ??''}}</td>
                <td>{{$suspectcase->chagas_result_screening ?? ''}}</td>
                <td>{{$suspectcase->chagas_result_confirmation_at ??''}}</td>
                <td>{{$suspectcase->chagas_result_confirmation}}</td>
                <td>{{$suspectcase->observation??''}}</td>

                <td>
                    @if($suspectcase->chagas_result_screening == 'Negativo')
                    <a href="{{ route('epi.chagas.printresultchagasnegative', $suspectcase) }}" target="_blank"><i class="fas fa-paperclip"></i>&nbsp</a>
                    @endif

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>


@endsection

@section('custom_js')

@endsection