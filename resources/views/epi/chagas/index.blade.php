@extends('layouts.app')

@section('content')
    <h4 class="mb-3">Listado de Solicitudes de Examenes de Chagas de {{ $tray }}</h4>
    @if ($tray === 'Pendientes de Recepción')
        <div class="col-12 col-md-2">
            <div class="input-group mb-3">
                <button class="btn btn-primary" id="btn_reception" form="mass_reception_form" type="submit"
                    title="Seleccione las muestras a recepcionar."> <i class="fas fa-inbox"></i> Recepcionar</button>

            </div>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-sm table-bordered" id="tabla_casos">
            <thead>
                <tr>
                    @if ($tray === 'Pendientes de Recepción')
                        <th>Selec.</th>
                    @endif
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
                </tr>
            </thead>



            <tbody id="tableCases">
                @foreach ($suspectcases as $suspectcase)
                    <tr>
                        @if ($tray === 'Pendientes de Recepción')
                            <td style="text-align:center;"><label for="chk_derivacion"></label><input type="checkbox" name="casos_seleccionados[]" id="chk_derivacion_{{ $suspectcase->id }}" class="select_checkboxs" value="{{ $suspectcase->id }}" />
 </td>
                        @endif
                        <td>{{ $suspectcase->id ?? '' }}

                            @can('Epi: Add Value')
                                @if ($tray === 'Todas las Solicitudes' and $suspectcase->reception_at === null)
                                    <form method="POST" class="form-inline"
                                        action="{{ route('epi.chagas.reception', $suspectcase) }}">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm btn-primary" title="Recepcionar"><i
                                                class="fas fa-inbox"></i></button>
                                    </form>
                                @endif
                                @if ($tray === 'Todas las Solicitudes' and $suspectcase->reception_at != null)
                                    <a href="{{ route('epi.chagas.edit', $suspectcase) }}" pclass="btn_edit"><i
                                            class="fas fa-edit"></i></a>
                                @endif
                                @if ($tray === 'Pendientes de Recepción' and $suspectcase->reception_at === null)
                                    <form method="POST" class="form-inline"
                                        action="{{ route('epi.chagas.reception', $suspectcase) }}">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm btn-primary" title="Recepcionar"><i
                                                class="fas fa-inbox"></i></button>
                                    </form>
                                @endif
                                @if ($tray === 'Pendientes de Resultado' and $suspectcase->reception_at != null)
                                    <a href="{{ route('epi.chagas.edit', $suspectcase) }}" pclass="btn_edit"><i
                                            class="fas fa-edit"></i></a>
                                @endif
                            @endcan

                        </td>
                        <td>{{ $suspectcase->research_group ?? '' }}</td>
                        <td>{{ $suspectcase->sample_at ? $suspectcase->sample_at : '' }}</td>
                        <td>{{ $suspectcase->organization->alias ?? '' }}</td>
                        <td>{{ $suspectcase->patient->OfficialFullName ?? '' }}
                            @if ($suspectcase->research_group == 'Gestante (+semana gestacional)')
                                <img align="center" src="{{ asset('images/pregnant.png') }}" width="24">
                            @endif
                        </td>
                        <td>
                            @if ($suspectcase->patient->identifierRun)
                                {{ $suspectcase->patient->identifierRun->value ?? '' }}-{{ $suspectcase->patient->identifierRun->dv }}
                            @else
                                {{ $suspectcase->patient->Identification->value ?? '' }}
                            @endif
                        </td>
                        <td>
                            {{ $suspectcase->patient->AgeString ?? '' }}
                        </td>
                        <td>{{ $suspectcase->patient->actualSex()->text ?? '' }}</td>
                        <td>{{ $suspectcase->patient->nationality->name ?? '' }}</td>
                        <td>
                            {{ $suspectcase->chagas_result_screening_at ?? '' }}
                        </td>
                        <td>
                            {{ $suspectcase->chagas_result_screening ?? '' }}
                            @if ($suspectcase->chagas_result_screening_file)
                                <a href="{{ route('epi.chagas.downloadFile', ['fileName' => $suspectcase->chagas_result_screening_file]) }}"
                                    target="_blank" data-toggle="tooltip" data-placement="top"
                                    data-original-title="{{ $suspectcase->id . 'pdf' }}">Descargar <i
                                        class="fas fa-paperclip"></i>&nbsp
                                </a>
                            @endif

                        </td>
                        <td>{{ $suspectcase->chagas_result_confirmation_at ?? '' }}</td>
                        <td>{{ $suspectcase->chagas_result_confirmation }}

                            @if ($suspectcase->chagas_result_confirmation_file)
                                <a href="{{ route('epi.chagas.downloadFile', ['fileName' => $suspectcase->chagas_result_confirmation_file]) }}"
                                    target="_blank" data-toggle="tooltip" data-placement="top"
                                    data-original-title="{{ $suspectcase->id . 'pdf' }}">Descargar <i
                                        class="fas fa-paperclip"></i>&nbsp
                                </a>
                            @endif


                        </td>
                        <td>{{ $suspectcase->observation ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $suspectcases->appends(request()->query())->links() }}
@endsection

@section('custom_js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#btn_reception').prop('disabled', true);

            // Habilitar el botón "Recepcionar" al hacer clic en una casilla de verificación
            $('input.select_checkboxs').change(function() {
                var checkedCount = $('input.select_checkboxs:checked').length;
                $('#btn_reception').prop('disabled', checkedCount === 0);
            });

            // Recepción masiva al hacer clic en el botón "Recepcionar"
            $('#btn_reception').click(function() {
                var selectedCases = [];
                $('input.select_checkboxs:checked').each(function() {
                    selectedCases.push($(this).val());
                });

                if (selectedCases.length > 0) {
                    var confirmed = confirm("¿Estás seguro de recepcionar las muestras seleccionadas?");
                    if (confirmed) {
                        var url = "{{ route('epi.chagas.massReception') }}";
                        var csrfToken = "{{ csrf_token() }}";

                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                casos_seleccionados: selectedCases,
                                _token: csrfToken
                            },
                            success: function(response) {
                                alert(response.message);
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    }
                }
            });
        });
    </script>
@endsection

