<div>
    <div class="form-row mt-3">

        <div class="form-group col-md-4">
            <label for="inputrut">RUT</label>
            <input type="text" class="form-control" placeholder="Ingrese el rut" wire:model.lazy="run"
                   wire:change="setDv()">

        </div>
        <div class="form-group col-md-1">
            <label for="inputdv">Dv</label>
            <input type="text" class="form-control" placeholder="Dv" wire:model="dv" readonly>
        </div>

        {{--        <div class="form-group col-md-4">--}}
        {{--            <label for="inputnombre">Nombre</label>--}}
        {{--            <input type="text" class="form-control" placeholder="Ingrese Nombre" wire:model.lazy="name">--}}
        {{--        </div>--}}

        <div class="form-group col-md-2">
            <label for="inputEmail4">&nbsp;</label>
            <button type="button" class="btn btn-primary form-control" wire:click="searchUser()">Buscar</button>
        </div>

        <div class="form-group col-md-1">
            <label for="inputEmail4">&nbsp;</label>
            <button type="button" class="btn btn-primary form-control" data-toggle="modal"
                    data-target="#searchUserModal">...
            </button>
        </div>
    </div>

    <div class="form-row mt-3">
        <div class="form-group col-md-6">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-info">
                    <tr>
                        <th scope="col">Nombre:</th>
                        <th scope="col">{{ ($user) ? $user->officialFullName : '' }}</th>

                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row">Identificación</th>
                        <td>{{($user) ? $user->identifierRun->value . '-' . $user->identifierRun->dv : ''}}</td>
                    </tr>

                    <tr>
                        <th scope="row">Edad</th>
                        <td>{{($user) ? \Carbon\Carbon::parse($user->birthday)->age .' años' : ''}}</td>
                    </tr>

                    <tr>
                        <th scope="row">Sexo</th>
                        <td colspan="2"> {{($user) ? $user->sex : '' }} </td>
                    </tr>

                    <tr>
                        <th scope="row">Dirección</th>
                        <td colspan="2"> {{($user) ? $user->officialFullAddress : ''}}  </td>
                    </tr>

                    <tr>
                        <th scope="row">Teléfono</th>
                        <td colspan="2">{{($user) ? $user->officialPhone : ''}}</td>
                    </tr>

                    <tr>
                        <th scope="row">Correo</th>
                        <td colspan="2">{{($user && $user->officialEmail) ? $user->officialEmail : ''}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="form-group col-md-6">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-info">

                    <tr>
                        <th scope="col">HISTORIAL DE CITAS MÉDICAS</th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                    </thead>
                    <tbody>

                    @if($appointmentsHistory)
                        @foreach($appointmentsHistory as $appointmentHistory)
                            <tr>
                                <th scope="row">{{$appointmentHistory->start . (($appointmentHistory->type->text == 'OVERBOOKING') ? ' (SC)' : '') }}</th>
                                <td>{{$appointmentHistory->theoreticalProgramming->specialty->specialty_name}}</td>
                                <td>
                                    <a href="{{ route('some.appointment_detail',$appointmentHistory->id) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <span class="fas fa-eye" aria-hidden="true"></span>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Desea cancelar la cita?') || event.stopImmediatePropagation();"
                                            wire:click="cancelAppointment({{$appointmentHistory->id}})"
                                        {{ ($appointmentHistory->status == 'cancelled' ? 'disabled' : '' ) }}><i
                                            class="fas fa-ban"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    </tbody>
                </table>
            </div>
        </div>

    </div>


    <div class="form-row">
        <div class="form-group col-md-1">
            <label for="for_type">Tipo</label>
            <select id="for_type" name="type" class="form-control" wire:model.lazy="type" required
                    wire:change="getPractitioners()">
                <option></option>
                <option>Médico</option>
                <option>No médico</option>
            </select>
        </div>

        <div class="form-group col-md-2">
            @if($specialties != null)
                <label for="for_specialty_id">Especialidad</label>
                <select id="for_specialty_id" name="specialty_id" class="form-control" wire:model.lazy="specialty_id"
                        wire:change="getPractitioners()"
                        required>
                    <option></option>
                    @foreach($specialties as $specialty)
                        <option value="{{$specialty->id}}">{{$specialty->specialty_name}}</option>
                    @endforeach
                </select>
            @endif

            @if($professions != null)
                <label for="for_profession_id">Profesión</label>
                <select id="for_profession_id" name="profession_id" class="form-control" wire:model.lazy="profession_id"
                        wire:change="getPractitioners"
                        required>
                    <option></option>
                    @foreach($professions as $profession)
                        <option value="{{$profession->id}}">{{$profession->profession_name}}</option>
                    @endforeach
                </select>
            @endif

            @if($specialties == null && $professions == null)
                <label for="for_profession_id">&nbsp;</label>
                <select class="form-control">
                    <option></option>
                </select>
            @endif

        </div>

        <div class="form-group col-md-2">
            <label for="for_practitioner_id">Funcionario</label>
            <select id="for_practitioner_id" name="practitioner_id" class="form-control"
                    wire:model.lazy="practitioner_id" required>
                <option></option>
                @if($practitioners != null)
                    @foreach($practitioners as $practitioner)
                        <option value="{{$practitioner->id}}">{{$practitioner->user->OfficialFullName}}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group col-md-2">
            <label for="for_location">Lugar</label>
            <select id="for_location" name="location" class="form-control"
            wire:model.lazy="selectedLocation">
                <option selected></option>
                @if($locations != null)
                    @foreach($locations as $location)
                        <option value="{{$location->id}}"> {{$location->alias}} </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group col-md-2">
            <label for="inputEmail4">Desde</label>
            <input type="date" class="form-control" id="inputEmail4" placeholder="Fecha inicio"
                   wire:model.lazy="appointments_from">
        </div>

        <div class="form-group col-md-2">
            <label for="inputEmail4">Hasta</label>
            <input type="date" class="form-control" id="inputEmail4" placeholder="Fecha fin"
                   wire:model.lazy="appointments_to">
        </div>

        <div class="form-group col-md-1">
            <label for="inputEmail4">&nbsp;</label>
            <button type="button" class="btn btn-primary form-control" wire:click="searchAppointments()">Buscar</button>
        </div>
        <fieldset class="form-group col-4">
            <label></label>
            <div class="form-group mt-1 ml-4">
                {{--                <input class="form-check-input" type="checkbox" id="gridCheck">--}}
                {{--                <label class="form-check-label" for="gridCheck">--}}
                {{--                    Proxima Fecha Disponible--}}
                {{--                </label>--}}
            </div>
        </fieldset>

    </div>


    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="inputEmail4">Prevision</label>
            <div class="input-group mb-3">
                <select id="inputState" class="form-control col-md-6">
                    <option selected>Prevision</option>
                    <option>Fonasa</option>
                    <option>Isapre</option>
                </select>
                <select id="inputState" class="form-control col-md-3">
                    <option selected>Tramo</option>
                    <option>A</option>
                    <option>B</option>
                    <option>C</option>
                    <option>D</option>
                </select>
                <button class="btn btn-primary col-md-3" type="button" id="button-addon1">Fonasa</button>
            </div>
        </div>

        <div class="form-group col-md-1">
            <label for="imputBool">Codigo</label>
            <input type="bool" class="form-control" id="inputBool" placeholder="">
        </div>
        <div class="form-group col-md-2">
            <label for="imputBool">Prestación</label>
            <input type="bool" class="form-control" id="inputBool" placeholder="">
        </div>
        <div class="form-group col-md-1">
            <label for="imputNumeric">N° Interconsulta</label>
            <input type="numeric" class="form-control" id="inputNumeric" placeholder="">
        </div>
        <div class="form-group col-md-2">
            <label for="inputDate">Fecha de Interconsulta</label>
            <input type="date" class="form-control" id="inputDate" placeholder="Fecha de interconsulta">
        </div>
        <div class="form-group col-md-2">
            <label for="inputEmail4">Procedencia</label>
            <select id="inputState" class="form-control">
                <option selected>Cesfam Videla</option>
                <option>Cesfam Aguirre</option>
                <option>Cesfam Guzmán</option>
                <option>Cesfam Sur</option>
            </select>
        </div>
    </div>


    <!--opcion asignar-->

    <div class="form-row ">
        <div class="form-group col-md-2">
            <label for="inputAsignar">&nbsp;</label>
            <button type="button" class="btn btn-primary form-control" wire:click="asignAppointment()">Asignar</button>
        </div>

        <div class="form-group col-md-2">
            <label for="for_observation_id">Instrucción para paciente</label>
            <select id="for_observation_id" name="observation_id" class="form-control" onchange="setPatientObservation(this)" >
                <option value=""></option>
                <option value="Debe presentarse 15 mins antes.">Debe presentarse 15 mins antes.</option>
                <option value="Debe presentar hora médica.">Debe presentar hora médica.</option>
            </select>
        </div>

        <div class="form-group col-md-8">
            <label for="for_patient_instruction">Instrucción para paciente</label>
            <textarea class="form-control" name="patient_instruction" id="for_patient_instruction" cols="10" rows="3" wire:model="patientInstruction"></textarea>
        </div>

    </div>
    <!-- fin opcion asignar-->
    <!--inicio tabla profesionales-->
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead class="table-info">
            <tr>
                <th scope="col">Sel.</th>
                <th scope="col">Sobrecupo</th>
                <th scope="col">Profesional</th>
                <th scope="col">Actividad</th>
                <th scope="col">Subactividad</th>
                <th scope="col">Hora</th>
                {{--                <th scope="col">Cupo</th>--}}
                {{--                <th scope="col">Sobre Cupo</th>--}}
                <th scope="col">Estado</th>
            </tr>
            </thead>
            <tbody>
            @if($appointments)

                @foreach($appointments as $key => $appointment)
                    <tr class="{{($appointment->status == 'booked') ? 'table-danger' : '' }}" >
                        <td>
                            <div class="form-check">
                                <input class="form-check-input " type="checkbox"
                                       value="{{$appointment->id}}"
                                       name="selectedAppointments[{{$key}}]"
                                       wire:model.defer="selectedAppointments"
                                       id="for_selected_appointments[{{$key}}]"
                                       {{$appointment->status == 'booked' ? 'disabled' : ''}}>
                            </div>
                        </td>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       value="{{$appointment->id}}"
                                       name="selectedOverbooking[{{$key}}]"
                                       wire:model.defer="selectedOverbookingAppointments">
                            </div>
                        </td>
                        <td>
{{--                            {{$appointment->theoreticalProgramming->user->officialFullName}}--}}
                                                        <label class="label"
                                                               for="for_selected_appointments[{{$key}}]">{{$appointment->theoreticalProgramming->user->officialFullName}}</label>
                        </td>

                        <td>{{$appointment->theoreticalProgramming->activity->activity_name}}</td>
                        <td>{{($appointment->theoreticalProgramming->subactivity) ? $appointment->theoreticalProgramming->subactivity->sub_activity_name : ''}}</td>
                        <td>{{$appointment->start}}</td>
                        {{--                        <td>3</td>--}}
                        {{--                        <td>2</td>--}}
                        <td>{{$appointment->status}}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>

    @livewire('some.search-user')

    <hr class="mt-3">

    {{--    @livewireScripts--}}

    <script>

        function setPatientObservation(select) {
            var textArea = document.getElementById('for_patient_instruction');
            textArea.value = textArea.value + ' ' + select.value;
            textArea.dispatchEvent(new Event('input'));
        }

        // window.livewire.on('userSelected', () => {
        //     // console.log('cierra modal');
        //     $('#searchUserModal').modal('hide');
        // })
        // window.addEventListener('closeModal', event => {
        //     console.log('cierra modal por browser event');
        //     $("#searchUserModal").modal('hide');
        // })
    </script>
</div>
