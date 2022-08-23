@extends('layouts.app')

@section('content')
<h4 class="mb-3">Seguimiento/Notificación Casos Positivos</h4>

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
                <th>Cantidad de Notificaciones</th>
                <th>Contacto</th>
            </tr>
        </thead>



        <tbody id="tableCases">
            @foreach($suspectcases as $suspectcase)
            <tr>
                <td>{{$suspectcase->id??''}}
                
                <a href="{{ route('epi.tracings.create') }}" pclass="btn_edit"><i class="fas fa-phone"></i></a>
                </td>
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
                <td>{{$suspectcase->chagas_result_screening_at ??''}}</td>
                <td>{{$suspectcase->chagas_result_screening ?? ''}}</td>
                <td>{{$suspectcase->chagas_result_confirmation_at ??''}}</td>
                <td>{{$suspectcase->chagas_result_confirmation ?? ''}}</td>
                <td>{{$suspectcase->observation??''}}</td>
                <td>
                    0
                </td>
                <td>
                <a class="btn btn-primary btn-sm" href="{{ route('epi.contacts.create',$suspectcase->patient) }}">
                <i class="fas fa-plus"></i> 
                </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection

@section('custom_js')

@endsection