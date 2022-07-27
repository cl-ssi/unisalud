<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>Mi información</span>
    <a class="d-flex align-items-center text-muted" data-bs-toggle="collapse" aria-expanded="false" aria-label="Mi información" href="#my-info" aria-controls="my-info">
        <span id="icon_my-info" data-feather="plus-circle"></span>
    </a>
</h6>
<ul class="nav flex-column collapse collapse-menu" id="my-info">
    <!-- <li class="nav-item">
        <a class="nav-link {{ active('profile.observation.index') }}" href="{{ route('profile.observation.index') }}">
        <span data-feather="user"></span>
        Mis exámenes<span class="sr-only"></span>
        </a>
    </li> -->
    <li class="nav-item">
        <a class="nav-link {{ active(['profile.show', 'profile.edit']) }}" href="{{ route('profile.show') }}">
            <span data-feather="user"></span>
            Mi perfíl<span class="sr-only"></span>
        </a>
    </li>
</ul>




@canany(['Developer', 'Administrator', 'Mp: user creator'])
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>Pacientes</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="Pacientes" href="#patients" aria-controls="patients">
        <span id="icon_patients" data-feather="plus-circle"></span>
    </a>
</h6>
<ul class="nav flex-column collapse collapse-menu" id="patients">
    <li class="nav-item">
        <a class="nav-link {{ active('patient.index') }}" href="{{ route('patient.index') }}">
            <span data-feather="users"></span>
            Buscar paciente<span class="sr-only">(current)</span>
        </a>
    </li>
    {{--@canany(['Administrator', 'Mp: user creator'])
    <li class="nav-item">
        <a class="nav-link {{ active('patient.create') }}" href="{{ route('patient.create') }}">
    <span data-feather="plus-circle"></span>
    Ingresar nuevo
    </a>
    </li>
    @endcanany--}}
    {{-- @if(App\Models\Fq\ContactUser::getAmIContact() > 0)
        <li class="nav-item">
            <a class="nav-link {{ active('fq.request.create') }}" href="{{ route('fq.request.create') }}">
    <span data-feather="plus-circle"></span>
    Solicitudes Pacientes FQ
    </a>
    </li>
    @else
    @canany(['Fq: Answer request', 'Fq: Answer request medicines'])
    <li class="nav-item">
        <a class="nav-link {{ active('fq.request.create') }}" href="{{ route('fq.request.index') }}">
            <span data-feather="plus-circle"></span>
            Solicitudes Pacientes FQ
        </a>
    </li>
    @endcanany
    @endif --}}

</ul>
@endcanany




@can('Some: user')
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>SOME</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="SOME" href="#some" aria-controls="some">
        <span id="icon_some" data-feather="plus-circle"></span>
    </a>
</h6>
<ul class="nav flex-column collapse collapse-menu" id="some">
    <li class="nav-item">
        <a class="nav-link {{ active('some.appointment') }}" href="{{ route('some.appointment') }}"><span data-feather="file-text"></span>Cita</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('some.reallocate') }}"><span data-feather="repeat"></span>Reasignación/Reserva</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{route('some.open_tprogrammer') }}"><span data-feather="calendar"></span>Aperturar agenda</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{route('some.agenda') }}"><span data-feather="calendar"></span>Gestor de agenda</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{route('some.reallocationPending') }}"><span data-feather="list"></span>Pendiente de reasignación</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{route('some.interconsultation') }}"><span data-feather="briefcase"></span>Interconsultas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{route('some.appointedAvailable') }}"><span data-feather="list"></span>Citado/Disponible</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{route('some.openPending') }}"><span data-feather="calendar"></span>Pendiente apertura</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ active('some.locations.index') }}" href="{{ route('some.locations.index') }}">
            <span data-feather="chevrons-right"></span>
            Locaciones
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ active('some.observations.index') }}" href="{{ route('some.observations.index') }}">
            <span data-feather="chevrons-right"></span>
            Observaciones
        </a>
    </li>
</ul>
@endcan




@can('Administrator')
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>Administrador</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="Administrador" href="#administrador" aria-controls="administrador">
        <span id="icon_administrador" data-feather="plus-circle"></span>
    </a>
</h6>
<ul class="nav flex-column collapse collapse-menu" id="administrador">
    <li class="nav-item">
        <a class="nav-link {{ active('parameter.permission.index') }}" href="{{ route('parameter.permission.index') }}">
            <span data-feather="lock"></span>
            Permisos<span class="sr-only">(current)</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ active('user.edit') }}" href="{{ route('user.edit',auth()->id()) }}">
            <span data-feather="unlock"></span>
            Mis permisos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ active('user.index') }}" href="{{ route('user.index')}}">
            <span data-feather="unlock"></span>
            Usuarios
        </a>
    </li>
</ul>
@endcan




@canany(['Mp: user'])
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>Programador médico</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="Programador" href="#programmer" aria-controls="programmer">
        <span id="icon_programmer" data-feather="plus-circle"></span>
    </a>
</h6>

<ul class="nav flex-column collapse collapse-menu" id="programmer">

    <!-- programador de pabellones -->
    <!-- @canany(['Mp: programador pabellon'])
    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.operating_room_programming.index') }}" href="{{ route('medical_programmer.operating_room_programming.index') }}">
        <span data-feather="chevrons-right"></span>
        Programador de pabellones<span class="sr-only">(current)</span>
        </a>
    </li>
    @endcanany -->

    <!-- <li class="nav-item">
        <a class="nav-link" href="{{ route('medical_programmer.theoretical_programming.proposal_programmer','tipo=1') }}">
        <span data-feather="chevrons-right"></span>
        Propuestas de programación<span class="sr-only">(current)</span>
        </a>
    </li> -->



    @canany(['Mp: programador'])
    <li class="nav-item">
        <a class="nav-link" href="{{ route('medical_programmer.programming_proposal.index') }}">
            <span data-feather="chevrons-right"></span>
            Propuestas de programación<span class="sr-only">(current)</span>
        </a>
    </li>
    @endcanany

    <!-- <li class="nav-item">
        <a class="nav-link" href="{{ route('medical_programmer.programming_proposal.programming_by_practioner') }}">
        <span data-feather="chevrons-right"></span>
        Programaciones por funcionario<span class="sr-only">(current)</span>
        </a>
    </li> -->

    <li class="nav-item">
        <a class="nav-link" href="{{ route('medical_programmer.programming_proposal.consolidated_programmings') }}">
            <span data-feather="chevrons-right"></span>
            Programaciones<span class="sr-only">(current)</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('medical_programmer.reports.pendingPractitionersReport') }}">
            <span data-feather="chevrons-right"></span>
            Reporte 1<span class="sr-only">(current)</span>
        </a>
    </li>


</ul>
@endcanany

<!-- programador teorico -->
<!-- @canany(['Mp: programacion teorica'])
    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.theoretical_programming.index') }}">
        <span data-feather="chevrons-right"></span>
        Programador teórico<span class="sr-only">(current)</span>
        </a>
    </li>
    <ul class="pl-4 nav flex-column">
      @canany(['Mp: programacion medica'])
      <li class="nav-item">
          <a class="nav-link" href="{{ route('medical_programmer.theoretical_programming.index','tipo=1') }}">
          <span data-feather="chevron-right"></span>
          Médico<span class="sr-only">(current)</span>
          </a>
      </li>
      @endcanany

      @canany(['Mp: programacion no medica'])
      <li class="nav-item">
          <a class="nav-link" href="{{ route('medical_programmer.theoretical_programming.index','tipo=2') }}">
          <span data-feather="chevron-right"></span>
          No médico<span class="sr-only">(current)</span>
          </a>
      </li>
      @endcanany
    </ul>
    @endcanany -->

<!-- programador real -->
<!-- <li class="nav-item">
        <a class="nav-link">
        <span data-feather="chevrons-right"></span>
        Programador Real<span class="sr-only">(current)</span>
        </a>
    </li>
    <ul class="pl-4 nav flex-column">
      <li class="nav-item">
          <a class="nav-link {{ active('medical_programmer.calendar_programming.index') }}" href="{{ route('medical_programmer.calendar_programming.index') }}">
          <span data-feather="chevron-right"></span>
          Pabellones<span class="sr-only">(current)</span>
          </a>
      </li>
      <li class="nav-item">
          <a class="nav-link {{ active('medical_programmer.calendar_programming.indexbox') }}" href="{{ route('medical_programmer.calendar_programming.indexbox') }}">
          <span data-feather="chevron-right"></span>
          Box's<span class="sr-only">(current)</span>
          </a>
      </li>
    </ul> -->


<!-- mantenedores -->
@canany(['Mp: mantenedores'])
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>Mantenedores</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="Mantenedores del programador" href="#mp_settings" aria-controls="mp_settings">
        <span id="icon_mp_settings" data-feather="plus-circle"></span>
    </a>
</h6>

<ul class="nav flex-column collapse collapse-menu" id="mp_settings">

    <li class="nav-item">
        <a class="nav-link" href="{{ route('parameter.organization.index','Todas las Organizaciones' ) }}">
            <span data-feather="chevrons-right"></span>
            Organizaciones
        </a>
    </li>



    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.rrhh.index') }}" href="{{ route('medical_programmer.rrhh.index') }}">
            <span data-feather="chevrons-right"></span>
            RRHH
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.contracts.index') }}" href="{{ route('medical_programmer.contracts.index') }}">
            <span data-feather="chevrons-right"></span>
            Contratos
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.operating_rooms.index') }}" href="{{ route('medical_programmer.operating_rooms.index') }}">
            <span data-feather="chevrons-right"></span>
            Pabellones
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.mother_activities.index') }}" href="{{ route('medical_programmer.mother_activities.index') }}">
            <span data-feather="chevrons-right"></span>
            Actividades Madre
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.activities.index') }}" href="{{ route('medical_programmer.activities.index') }}">
            <span data-feather="chevrons-right"></span>
            Actividades
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.subactivities.index') }}" href="{{ route('medical_programmer.subactivities.index') }}">
            <span data-feather="chevrons-right"></span>
            Sub-actividades
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.services.index') }}" href="{{ route('medical_programmer.services.index') }}">
            <span data-feather="chevrons-right"></span>
            Servicios
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.specialties.index') }}" href="{{ route('medical_programmer.specialties.index') }}">
            <span data-feather="chevrons-right"></span>
            Especialidades
            <!-- (Rdtos sugeridos) -->
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('medical_programmer.professions.index') }}" href="{{ route('medical_programmer.professions.index') }}">
            <span data-feather="chevrons-right"></span>
            Profesiones
            <!-- (Rdtos sugeridos) -->
        </a>
    </li>

    <!-- <li class="nav-item">
    <a class="nav-link {{ active('medical_programmer.cutoffdates.index') }}" href="{{ route('medical_programmer.cutoffdates.index') }}">
    <span data-feather="chevrons-right"></span>
    Fechas de corte
    </a>
    </li> -->

    <!-- <li class="nav-item">
    <a class="nav-link {{ active('medical_programmer.unscheduled_programming.index') }}" href="{{ route('medical_programmer.unscheduled_programming.index') }}">
    <span data-feather="chevrons-right"></span>
    Programación
    </a>
    </li> -->

    <!-- <li class="nav-item">
    <a class="nav-link {{ active('medical_programmer.clone.index') }}" href="{{ route('medical_programmer.clone.index') }}">
    <span data-feather="chevrons-right"></span>
    Clonar
    </a>
    </li> -->

</ul>
@endcanany



<!-- Epi -->
@can('Epi: Create')
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>Epidemiología</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="Epidemiología" href="#epidemiology" aria-controls="epidemiology">
        <span id="icon_epidemiology" data-feather="plus-circle"></span>
    </a>
</h6>
<ul class="nav flex-column collapse collapse-menu" id="epidemiology">
    <li class="nav-item">
        <a class="nav-link {{ active('patient.index') }}" href="{{ route('patient.index') }}">
            <span data-feather="chevrons-right"></span>
            Solicitud Examen Chagas<span class="sr-only"></span>
        </a>
    </li>

    
    <li class="nav-item">
        <a class="nav-link {{ active('epi.chagas.index','Mi Organización') }}"  href="{{ route('epi.chagas.index','Mi Organización') }}">
            <span data-feather="chevrons-right"></span>
            Sol. Chagas de mi Organización<span class="sr-only"></span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link"  href="{{ route('epi.tracings.index') }}">
            <span data-feather="chevrons-right"></span>
            Seguimiento/Notificación<span class="sr-only"></span>
        </a>
    </li>
</ul>
@endcan


@can('Epi: Add Value')
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>Laboratorios</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="Laboratorio" href="#laboratory" aria-controls="laboratory">
        <span id="icon_epidemiology" data-feather="plus-circle"></span>
    </a>
</h6>
<ul class="nav flex-column collapse collapse-menu" id="laboratory">

    <li class="nav-item">
        <a class="nav-link" href="{{ route('parameter.organization.index','Laboratorios' ) }}">
            <span data-feather="chevrons-right"></span>
            Laboratorios
        </a>
    </li>


    <li class="nav-item">
        <a class="nav-link {{ active('epi.chagas.index','Todas las Solicitudes') }}" href="{{ route('epi.chagas.index','Todas las Solicitudes') }}">
            <span data-feather="chevrons-right"></span>
            Todas las Solicitudes Chagas<span class="sr-only"></span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('epi.chagas.index','Pendientes de Recepción') }}" href="{{ route('epi.chagas.index','Pendientes de Recepción') }}">
            <span data-feather="chevrons-right"></span>
            Pendientes de Recepción<span class="sr-only"></span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active('epi.chagas.index','Pendientes de Resultado') }}" href="{{ route('epi.chagas.index','Pendientes de Resultado') }}">
            <span data-feather="chevrons-right"></span>
            Pendientes de Resultado<span class="sr-only"></span>
        </a>
    </li>
</ul>
@endcan


<!-- ausencias -->
@can('Administrator')
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>Ausentismos</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="Ausentismos" href="#absences" aria-controls="absences">
        <span id="icon_absences" data-feather="plus-circle"></span>
    </a>
</h6>
<ul class="nav flex-column collapse collapse-menu" id="absences">
    <li class="nav-item">
        <a class="nav-link {{ active('absences.index') }}" href="{{ route('absences.index') }}">
            <span data-feather="chevrons-right"></span>
            Listar ausentismos<span class="sr-only"></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ active('absences.create') }}" href="{{ route('absences.create') }}">
            <span data-feather="chevrons-right"></span>
            Registrar nueva ausencia<span class="sr-only"></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ active('absences.load') }}" href="{{ route('absences.load') }}">
            <span data-feather="chevrons-right"></span>
            Importar ausencias<span class="sr-only"></span>
        </a>
    </li>
</ul>
@endcan




<!--SAMU-->
{{--
<ul class="nav flex-column">
    @canany(['SAMU'])
    <!--li class="nav-item">
    <a class="nav-link {{ active('samu.welcome') }}" href="{{ route('samu.welcome') }}">
        <i class="fas fa-ambulance"></i> SAMU
    </a>
</li-->
@endcanany

</ul>
--}}
<!--SAMU-->

@can('Developer')
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>Desarrollador</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="Desarrollador" href="#desarrollador" aria-controls="desarrollador">
        <span id="icon_administrador" data-feather="plus-circle"></span>
    </a>
</h6>
<ul class="nav flex-column collapse collapse-menu" id="administrador">
    <li class="nav-item">
        <a class="nav-link {{ active('developer.artisan') }}" href="{{ route('developer.artisan') }}">
            <span data-feather="lock"></span>
            Artisan<span class="sr-only">(current)</span>
        </a>
    </li>
</ul>
@endcan



<!-- aps -->
<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-1 mb-1 text-muted">
    <span>APS</span>
    <a class="d-flex align-items-center text-muted" data-toggle="collapse" aria-expanded="false" aria-label="Seremia" href="#seremia" aria-controls="absences">
        <span id="icon_absences" data-feather="plus-circle"></span>
    </a>
</h6>
@can('APS: Minor authorizations')
    <ul class="nav flex-column collapse collapse-menu" id="seremia">
        <li class="nav-item">
            <a class="nav-link {{ active('aps.authorization_types.index') }}" href="{{ route('aps.authorization_types.index') }}">
                <span data-feather="chevrons-right"></span>
                Tipos de autorizaciones<span class="sr-only"></span>
            </a>
        </li>
    </ul>
    <ul class="nav flex-column collapse collapse-menu" id="seremia">
        <li class="nav-item">
            <a class="nav-link {{ active('aps.minor_authorizations.index') }}" href="{{ route('aps.minor_authorizations.index') }}">
                <span data-feather="chevrons-right"></span>
                Listado de autorizaciones<span class="sr-only"></span>
            </a>
        </li>
    </ul>
@else
    <ul class="nav flex-column collapse collapse-menu" id="seremia">
        <li class="nav-item">
            <a class="nav-link {{ active('aps.minor_authorizations.parents_index') }}" href="{{ route('aps.minor_authorizations.parents_index') }}">
                <span data-feather="chevrons-right"></span>
                Mis autorizaciones<span class="sr-only"></span>
            </a>
        </li>
    </ul>
@endcan


<ul class="nav flex-column">
    <li class="nav-item border-top">
        @if(session()->has('god'))
            <a class="nav-link" href="{{ route('user.switch', session('god')) }}">
                <span class="text-danger" data-feather="eye"></span>
                God Like
            </a>
        @endif
        <a class="nav-link" href="{{ route('claveunica.logout') }}">
            <span data-feather="log-out"></span>
            Cerrar sesión
        </a>
    </li>
</ul>