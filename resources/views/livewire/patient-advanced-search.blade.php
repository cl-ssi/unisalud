<div>
    <form wire:submit.prevent="search">
        <div class="form-row pb-2">
            <div class="col-6 col-md-6">
                <input type="text" class="form-control" placeholder="Autenticación sin digito verificador" wire:model.lazy="searchByIdentifier" autocomplete="off">
            </div>
            <div class="col-6 col-md-6 mb-2">
                <input type="text" class="form-control" placeholder="Nombre y/o apellido" wire:model.lazy="searchByHumanName" autocomplete="off">
            </div>
            <div class="col-6 col-md-6">
                <input type="text" class="form-control" placeholder="Domicilio" wire:model.lazy="searchByAddress" autocomplete="off">
            </div>
            <div class="col-6 col-md-6 mb-2">
                <input type="text" class="form-control" placeholder="Teléfono, celular o e-mail" wire:model.lazy="searchByContactPoint" autocomplete="off">
            </div>
            <div class="col-12 col-md-12">
                <button type="button" class="btn btn-secondary mb-2 float-left" wire:click="clean">Limpiar</button>
                <button type="submit" class="btn btn-primary mb-2 float-right"><i class="fa fa-search"></i> Buscar</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead class="table-info">
                <tr>
                    <th scope="col">Nombre:</th>
                    <th scope="col">Run o Identificación</th>
                    <th scope="col">Edad</th>
                    <th scope="col">Sexo</th>
                    <th scope="col">Dirección</th>
                    <th scope="col">Teléfono</th>
                    <th scope="col">Correo</th>
                    <th scope="col">Selec.</th>
                    @can('Epi: Create')
                    <th scope="col">Añadir Caso Sospecha</th>
                    <th scope="col">Añadir Contacto</th>
                    @endcan
            </thead>
            <tbody>
                @if($patients)
                    @forelse($patients as $patient)
                    <tr>
                        <td>{{ ($patient) ? $patient->officialFullName : '' }}</td>
                        <td>{{($patient) ? $patient->Identification->value:'' }}
                        {{($patient->IdentifierRun)?'-'.$patient->identifierRun->dv:'' }}
                        </td>
                        <td>{{($patient) ? $patient->ageString : ''}}</td>
                        <td>{{($patient) ? $patient->actualSex : '' }}</td>
                        <td>{{($patient) ? $patient->officialFullAddress : ''}}</td>
                        <td>{{($patient) ? $patient->officialPhone : ''}}</td>
                        <td>{{($patient && $patient->officialEmail) ? $patient->officialEmail : ''}}</td>
                        <td><a class="btn-primary btn-sm" href="{{ route('patient.edit',$patient->id)}}" title="Editar"> <i class="fas fa-edit"></i> </a></td>
                        @can('Epi: Create')
                        <td>
                            <a href="{{ route('epi.chagas.create',$patient) }}"><i class="fas fa-viruses"></i></a>
                        </td>
                        <td>
                            <a class="btn btn-primary btn-sm" href="" data-toggle="modal" data-target="#exampleModal">
                                <i class="fas fa-people-arrows"></i> 
                            </a>
                            {{$original_id}}
                            @include('patients.modals.create_contact')
                        </td>
                        @endcan
                    </tr>
                    @empty
                        <tr><th scope="row" colspan="8" class="text-center">No hay coincidencias con la búsqueda <a class="btn-primary btn-sm" href="{{ route('patient.create')}}"> <i class="fas fa-user-plus"></i> Ingresar nuevo paciente</a></td></th>
                    @endforelse
                    @if($patients->count() > 0) <tr><th scope="row" colspan="8" class="text-center">Si ninguno en la búsqueda corresponde al paciente que estas buscando <a class="btn-primary btn-sm" href="{{ route('patient.create')}}"> <i class="fas fa-user-plus"></i> Ingresar nuevo paciente</a></td></th> @endif
                @endif
            </tbody>
        </table>
    </div>
</div>
