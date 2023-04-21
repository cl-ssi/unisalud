@extends('layouts.app')

@section('content')
<h4 class="mb-3">Listado de Solicitudes de Examenes de Chagas de {{$tray}}</h4>

<div class="table-responsive">
    <table class="table table-sm table-bordered" id="tabla_casos">
        <thead>
            <tr>
                <th nowrap>ID</th>
                <th>Grupo de Pesquisa</th>
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
                <!-- <th>Descargar resultado (Bosquejo)</th> -->
            </tr>
        </thead>



        <tbody id="tableCases">
            @foreach($suspectcases as $suspectcase)
            <tr>
                <td>{{$suspectcase->id??''}}

                    @can('Epi: Add Value')
                    @if($tray === 'Todas las Solicitudes' and $suspectcase->reception_at === null)
                    <form method="POST" class="form-inline" action="{{ route('epi.chagas.reception',$suspectcase) }}">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-sm btn-primary" title="Recepcionar"><i class="fas fa-inbox"></i></button>
                    </form>
                    @endif
                    @if($tray === 'Todas las Solicitudes' and $suspectcase->reception_at != null)
                    <a href="{{ route('epi.chagas.edit',$suspectcase) }}" pclass="btn_edit"><i class="fas fa-edit"></i></a>
                    @endif
                    @if($tray === 'Pendientes de Recepción' and $suspectcase->reception_at === null)
                    <form method="POST" class="form-inline" action="{{ route('epi.chagas.reception',$suspectcase) }}">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-sm btn-primary" title="Recepcionar"><i class="fas fa-inbox"></i></button>
                    </form>
                    @endif
                    @if($tray === 'Pendientes de Resultado' and $suspectcase->reception_at <> null)
                        <a href="{{ route('epi.chagas.edit',$suspectcase) }}" pclass="btn_edit"><i class="fas fa-edit"></i></a>
                        @endif
                        @endcan

                </td>
                <td>{{$suspectcase->research_group?? ''}}</td>
                <td>{{$suspectcase->sample_at? $suspectcase->sample_at: ''}}</td>
                <td>{{$suspectcase->organization->alias??''}}</td>
                <td>{{$suspectcase->patient->OfficialFullName ??''}}
                    @if($suspectcase->research_group == "Gestante (+semana gestacional)") <img align="center" src="{{ asset('images/pregnant.png') }}" width="24"> @endif
                </td>
                <td>@if($suspectcase->patient->identifierRun)
                    {{$suspectcase->patient->identifierRun->value ??''}}-{{$suspectcase->patient->identifierRun->dv}}
                    @else
                    {{ $suspectcase->patient->Identification->value ??''}}
                    @endif
                </td>
                <td>
                    {{ $suspectcase->patient->AgeString ?? '' }}
                </td>
                <td>{{$suspectcase->patient->actualSex()->text ??''}}</td>
                <td>{{$suspectcase->patient->nationality->name ??''}}</td>
                <td>
                    {{$suspectcase->chagas_result_screening_at ??''}}
                </td>
                <td>
                    {{$suspectcase->chagas_result_screening ?? ''}}
                    @if($suspectcase->chagas_result_screening_file)
                    <a href="{{ route('epi.chagas.downloadscreening', $suspectcase->id) }}" target="_blank" data-toggle="tooltip" data-placement="top" data-original-title="{{ $suspectcase->id . 'pdf' }}">Descargar <i class="fas fa-paperclip"></i>&nbsp
                    </a>
                    @endif

                </td>
                <td>{{$suspectcase->chagas_result_confirmation_at ??''}}</td>
                <td>{{$suspectcase->chagas_result_confirmation}}

                    @if($suspectcase->chagas_result_confirmation_file)
                    <a href="{{ route('epi.chagas.downloadscreening', $suspectcase->id) }}" target="_blank" data-toggle="tooltip" data-placement="top" data-original-title="{{ $suspectcase->id . 'pdf' }}">Descargar <i class="fas fa-paperclip"></i>&nbsp
                    </a>
                    @endif


                </td>
                <td>{{$suspectcase->observation??''}}</td>

                <!-- <td>
                    @if($suspectcase->chagas_result_screening == 'Negativo')
                    <a href="{{ route('epi.chagas.printresultchagasnegative', $suspectcase) }}" target="_blank"><i class="fas fa-paperclip"></i>&nbsp</a>
                    @endif

                </td> -->
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $suspectcases->appends(request()->query())->links() }}


@endsection

@section('custom_js')

@endsection