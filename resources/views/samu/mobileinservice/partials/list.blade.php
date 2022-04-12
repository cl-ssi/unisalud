<div class="table-responsive">
    <table class="table table-bordered">
        <tbody>
            @foreach($mobilesInService->reverse() as $mis)
            <tr class="table-secondary">
                <th width="90"></th>
                <th>Movil</th>
                <th>Posición</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>O2 central</th>
                <th>Observación</th>
                <th>Almuerzo</th>
                <th width="54"></th>
            </tr>

            <tr>
                <td>
                @if($mis->shift->status == true)
                    <a href="{{ route('samu.mobileinservice.edit',$mis) }}">
                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> {{ $mis->id }}</button>
                    </a>
                @endif
                </td>
                <td><b>{{ $mis->mobile->code }} {{ $mis->mobile->name }}</b></td>
                <td>{{ $mis->position }}</td>
                <td>{{ optional($mis->type)->name }}</td>
                <td>{{ $mis->status ? 'Activo' : 'Inactivo'  }}</td>
                <td>{{ $mis->o2 }}</td>
                <td>{{ $mis->observation }}</td>
                <td>
                    @if($editLunch)
                        @livewire('samu.lunch',['mis' => $mis])
                    @endif
                </td>
                <td width="50">
                    @if($mis->shift->status)
                    <form method="POST" action="{{ route('samu.mobileinservice.destroy' , $mis) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                    @endif
                </td>
            </tr>

            <tr>
                <td colspan="9">
                    @if($mis->shift->status AND auth()->user()->cannot('SAMU auditor') )
                        @livewire('samu.mobile-crew',['mobileInService' => $mis])
                    @else
                        <div class="row">
                            <div class="col-md-8 col-12">
                            <h4>Tripulación</h4>

                            <table class="table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Funcionario</th>
                                        <th>Función</th>
                                        <th>Asume</th>
                                        <th>Se retira</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mis->crew as $tripulant)
                                    <tr>
                                        <td>{{ $tripulant->officialFullName }}</td>
                                        <td>{{ $tripulant->pivot->jobType->name }}</td>
                                        <td>{{ $tripulant->pivot->assumes_at }}</td>
                                        <td>{{ $tripulant->pivot->leaves_at }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    @endif

                </td>
            </tr>
            @endforeach

        </tbody>
    </table>
</div>