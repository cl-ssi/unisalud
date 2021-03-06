<div>
    <div class="form-row mt-3">

        <div class="form-group col-md-3">
            <label for="for_type">Tipo</label>
            <select id="for_type" name="type" class="form-control" wire:model.lazy="typeFrom" required
                    wire:change="getPractitionersFrom()">
                <option></option>
                <option>Médico</option>
                <option>No médico</option>
            </select>
        </div>

        <div class="form-group col-md-3">
            @if($specialtiesFrom != null)
                <label for="for_specialty_id">Especialidad</label>
                <select id="for_specialty_id" name="specialty_id" class="form-control"
                        wire:model.lazy="selectedSpecialtyIdFrom"
                        wire:change="getPractitionersFrom()"
                        required>
                    <option></option>
                    @foreach($specialtiesFrom as $specialty)
                        <option value="{{$specialty->id}}">{{$specialty->specialty_name}}</option>
                    @endforeach
                </select>
            @endif

            @if($professionsFrom != null)
                <label for="for_profession_id">Profesión</label>
                <select id="for_profession_id" name="profession_id" class="form-control"
                        wire:model.lazy="selectedProfessionIdFrom"
                        wire:change="getPractitionersFrom()"
                        required>
                    <option></option>
                    @foreach($professionsFrom as $profession)
                        <option value="{{$profession->id}}">{{$profession->profession_name}}</option>
                    @endforeach
                </select>
            @endif

            @if($specialtiesFrom == null && $professionsFrom == null)
                <label for="for_profession_id">&nbsp;</label>
                <select class="form-control">
                    <option></option>
                </select>
            @endif

        </div>

        <div class="form-group col-md-3">
            <label for="for_practitioner_id">Funcionario</label>
            <select id="for_practitioner_id" name="practitioner_id" class="form-control"
                    wire:model.lazy="selectedPractitionerIdFrom" required>
                <option></option>
                @if($practitionersFrom != null)
                    @foreach($practitionersFrom as $practitioner)
                        <option value="{{$practitioner->id}}">{{$practitioner->user->OfficialFullName}}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group col-md-2">
            <label for="inputEmail4">Fecha </label>
            <input type="date" class="form-control" id="inputEmail4" placeholder="Ingrese Fecha"
                   wire:model.lazy="selectedDateFrom">
        </div>
        <div class="form-group col-md-1">
            <label for="inputEmail4">&nbsp;</label>
            <button type="button" class="btn btn-primary form-control" wire:click="getAppointmentsFrom()">Buscar</button>
        </div>

    </div>
    <!--tabla doctor-->
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
            <tr class="table-info">
                <th scope="col">{{ ($selectedPractitionerFrom) ? $selectedPractitionerFrom->officialFullName : ''}}</th>
                <th scope="col">USUARIO</th>
            </tr>
            </thead>

            <tbody>


            @if($appointments && $appointments->count() > 0)
                @foreach($appointments as $key => $appointment)

                    <tr>
                        {{--                        <th scope="row"></th>--}}
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       value="{{$appointment->id}}"
                                       wire:model.defer="selectedAppointmentsFrom"
                                       id="for_selected_appointments_from[{{$key}}]"/>

                                <label class="label"
                                       for="for_selected_appointments_from[{{$key}}]">{{$appointment->start}}</label>
                            </div>
                        </td>
                        <td>
                            {{$appointment->users->first()->officialFullName}}

                        </td>

                    </tr>
                @endforeach
            @endif

            </tbody>
        </table>
    </div>
    <!--fin tabla doctor-->
    <div class="form-row">

        <div class="form-group col-md-10">
        </div>
        <div class="form-group col-md-2">
            <button type="button" class="btn btn-danger form-control">SUSPENDER</button>
        </div>
    </div>

    <div class="form-row">

        <div class="form-group col-md-3">
            <label for="">Tipo</label>
            <select id="for_type" name="type_to" class="form-control" wire:model.lazy="typeTo" required
                    wire:change="getPractitionersTo()">
                <option></option>
                <option>Médico</option>
                <option>No médico</option>
            </select>
        </div>

        <div class="form-group col-md-3">
            @if($specialtiesTo != null)
                <label for="for_specialty_id">Especialidad</label>
                <select id="for_specialty_id" name="specialty_id" class="form-control"
                        wire:model.lazy="selectedSpecialtyIdTo"
                        wire:change="getPractitionersTo()"
                        required>
                    <option></option>
                    @foreach($specialtiesTo as $specialty)
                        <option value="{{$specialty->id}}">{{$specialty->specialty_name}}</option>
                    @endforeach
                </select>
            @endif


            @if($professionsTo != null)
                <label for="for_profession_id">Profesión</label>
                <select id="for_profession_id" name="profession_id" class="form-control"
                        wire:model.lazy="selectedProfessionIdTo"
                        wire:change="getPractitionersTo()"
                        required>
                    <option></option>
                    @foreach($professionsTo as $profession)
                        <option value="{{$profession->id}}">{{$profession->profession_name}}</option>
                    @endforeach
                </select>
            @endif


            @if($specialtiesTo == null && $professionsTo == null)
                <label for="for_profession_id">&nbsp;</label>
                <select class="form-control">
                    <option></option>
                </select>
            @endif
        </div>

        <div class="form-group col-md-3">
            <label for="for_practitioner_id">Funcionario</label>
            <select id="for_practitioner_id" name="practitioner_id" class="form-control"
                    wire:model.lazy="selectedPractitionerIdTo" required>
                <option></option>
                @if($practitionersTo != null)
                    @foreach($practitionersTo as $practitioner)
                        <option value="{{$practitioner->id}}">{{$practitioner->user->OfficialFullName}}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group col-md-2">
            <label for="inputEmail4">Fecha </label>
            <input type="date" class="form-control" id="inputEmail4" placeholder="Ingrese Fecha">
        </div>
        <div class="form-group col-md-1">
            <label for="inputEmail4">&nbsp;</label>
            <button type="button" class="btn btn-primary form-control">Buscar</button>
        </div>

        <!--CHECK-->
        <div class="form-group col-md-1 mt-1 ml-4">

            <input type="checkbox" class="form-check-input" id="exampleCheck1">
            <label class="form-check-label" for="exampleCheck1">Proxima</label>
        </div>
        <!--CHECK-->
    </div>
    <!--anterior,siguiente,traspasar-->
    <div class="form-row">

        <div class="form-group col-md-2">
            <button type="button" class="btn btn-outline-primary">ANTERIOR <<</button>

            </select>
        </div>
        <div class="form-group col-md-2">
            <button type="button" class="btn btn-outline-primary">SIGUIENTE >></button>
        </div>
        <div class="form-group col-md-6">
        </div>
        <div class="form-group col-md-2">
            <button type="button" class="btn btn-primary form-control">TRASPASAR</button>
        </div>


    </div>
    <!-- fin anterior,siguiente,traspasar-->


    <!--tabla agenda-->
    <div class="table-responsive">

        <table class="table table-sm table-hover">
            <thead>
            <tr class="table-info">
                <th scope="col">JUEVES 27 DE MAYO</th>
                <th scope="col">ESPECIALIDAD</th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col">CUPOS</th>
                <th scope="col">SOBRE CUPO</th>
                <th scope="col">ESTADO</th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <th scope="row">Dra Macarena Lopez</th>
                <td>Neurología</td>
                <td>
                    <label class="form-check-label" for="invalidCheck2">8:00</label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" value="" id="invalidCheck2" required>
                </td>
                <td>2</td>
                <td>1</td>
                <td>DISPONIBLE</td>


            </tr>
            <tr>
                <th scope="row">Dr. Daniel Suarez</th>
                <td>Neurología</td>
                <td></td>
                <td>
                    <input class="form-check-input" type="checkbox" value="" id="invalidCheck2" required>
                    <label class="form-check-label" for="invalidCheck2"></label>

                </td>
                <td>3</td>

                <td>0</td>
                <td>DISPONIBLE</td>

            </tr>
            <thead>
            <tr class="table-info">
                <th scope="col">VIERNES 28 DE MAYO</th>
                <th scope="col">ESPECIALIDAD</th>
                <th scope="col"></th>
                <th scope="col-md-1"></th>
                <th scope="col">CUPOS</th>
                <th scope="col">SOBRE CUPO</th>
                <th scope="col">ESTADO</th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <th scope="row">Dr. Jorge Lopez</th>
                <td>Neurología</td>
                <td>
                    <label class="form-check-label" for="invalidCheck2">9:00</label>

                </td>
                <td>
                    <input class="form-check-input" type="checkbox" value="" id="invalidCheck2" required>
                </td>
                <td>3</td>
                <td>1</td>
                <td>DISPONIBLE</td>


            </tr>
            <tr>
                <th scope="row">Dr. Daniel Suarez</th>
                <td>Neurología</td>
                <td></td>
                <td>
                    <input class="form-check-input" type="checkbox" value="" id="invalidCheck2" required>
                    <label class="form-check-label" for="invalidCheck2"></label>

                </td>
                <td>3</td>
                <td>0</td>
                <td>DISPONIBLE</td>

            </tr>

            </tbody>
        </table>
    </div>
</div>
