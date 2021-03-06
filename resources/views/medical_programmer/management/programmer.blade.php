@extends('layouts.app')

@section('content')

<link href="{{ asset('css/bootstrap-select.min.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('css/animate.css') }}" rel="stylesheet" type="text/css"/>

<link href='{{asset('assets/fullcalendar/packages/core/main.css')}}' rel='stylesheet'/>
<link href='{{asset('assets/fullcalendar/packages/daygrid/main.css')}}' rel='stylesheet'/>
<link href='{{asset('assets/fullcalendar/packages/timegrid/main.css')}}' rel='stylesheet'/>
<link href='{{asset('assets/fullcalendar/packages/list/main.css')}}' rel='stylesheet'/>

<script src='{{asset('assets/fullcalendar/packages/core/main.js')}}'></script>

<style type="text/css" rel="stylesheet">
#page-loader {
position: fixed;
top: 0;
left: 0;
width: 100%;
height: 100%;
z-index: 1000;
background: #FFF none repeat scroll 0% 0%;
z-index: 99999;
}
#page-loader .preloader-interior {
display: block;
position: relative;
left: 50%;
top: 50%;
width: 189px;
height: 171px;
margin: -75px 0 0 -75px;
background-image: url("{{asset('images/logo_rgb.png')}}");
-webkit-animation: heartbeat 1s infinite;
}

#page-loader .preloader-interior:before {
content: "";
position: absolute;
top: 5px;
left: 5px;
right: 5px;
bottom: 5px;
-webkit-animation: heartbeat 1s infinite;
}

@keyframes heartbeat
{
  0%{transform: scale( .75 );}
  20%{transform: scale( 1 );}
  40%{transform: scale( .75 );}
  60%{transform: scale( 1 );}
  80%{transform: scale( .75 );}
  100%{transform: scale( .75 );}
}

</style>

<h3 class="mb-3">Programación de Horas de Pabellon.</h3>

<hr>

<h5 class="mb-3"></h5>

<form method="GET" id="form" class="form-horizontal" action="{{ route('medical_programmer.calendar_programming.index') }}">

<div align="right">
  <p>
    <input name="date2" type="date" onchange="this.form.submit()">
    <button type="button" id='prev'>Anterior</button>
    <button type="button" id='next'>Próximo</button>
  </p>
</div>

    {{-- <div align="right">
      <p><input name="date2" type="date" onchange="this.form.submit()"></p>
    </div> --}}

  {{-- <input type="hidden" id="date" name="date"/>
  <input type="hidden" id="year" name="year" value="{{$request->year}}"/>
  <input type="hidden" id="rut" name="rut" value="{{$request->rut}}"/>
</form>

<form method="GET" id="form" class="form-horizontal" action="{{ route('medical_programmer.calendar_programming.index') }}"> --}}
  <input type="hidden" id="date" name="date"/>
  <div class="row">
    <fieldset class="form-group col-3">
        <label for="for_unit_code">Año</label>
        <select name="year" id="for_year" class="form-control" required="" onchange="this.form.submit()">
          <option value="2020" {{ 2020 == $request->year ? 'selected' : '' }}>2020</option>
          <option value="2021" {{ 2021 == $request->year ? 'selected' : '' }}>2021</option>
          <option value="2022" {{ 2022 == $request->year ? 'selected' : '' }}>2022</option>
          <option value="2023" {{ 2023 == $request->year ? 'selected' : '' }}>2023</option>
          <option value="2024" {{ 2024 == $request->year ? 'selected' : '' }}>2024</option>
          <option value="2025" {{ 2025 == $request->year ? 'selected' : '' }}>2025</option>
        </select>
    </fieldset>

    <fieldset class="form-group col">
        <label for="for_rut">Especialista</label>
        <select name="rut" id="rut" class="form-control selectpicker" required="" onchange="this.form.submit()" data-live-search="true" data-size="5">
          <option value="0">Todos</option>
          @foreach($rrhhs as $rrhh)
            <option value="{{$rrhh->id}}" {{ $rrhh->id == $request->rut ? 'selected' : '' }}>{{$rrhh->OfficialFullName}}</option>
          @endforeach
        </select>
    </fieldset>

    <fieldset class="form-group col-3">
    <label for="for_box">Pabellones</label>
    <select name="operating_rooms[]" id="for_operating_rooms" class="form-control selectpicker" onchange="this.form.submit()" data-live-search="true" multiple="" data-size="10" title="Seleccione..." multiple data-actions-box="true" required>

        @if ($request->operating_rooms == null)
            @foreach($operatingRoomsTotal as $operatingRoom)
                <option value="{{ $operatingRoom->id }}" selected="selected" >{{ $operatingRoom->name }}</option>
            @endforeach
        @else
            @foreach($operatingRoomsTotal as $operatingRoom)
                <option value="{{ $operatingRoom->id }}" @if(in_array($operatingRoom->id, (array)$request->operating_rooms)) selected="selected" @endif>{{ $operatingRoom->name }}</option>
            @endforeach
        @endif
    </select>
    </fieldset>

  </div>
</form>

  <div id='wrap'>

    <div id='external-events'>
      <div id='external-events-list'>
        {{--<h4>Oftalmólogos</h4>
        <div class='fc-event' style="background-color: #6C97AB;" data-color='#6C97AB' data-event='{"title":"Sebastian Pedreros", "color": "#6C97AB", "id":"444",  "description":"4"}'>Sebastian Pedreros: <span id="444"></span></div>
        <div class='fc-event' style="background-color: red;" data-color='red' data-event='{"title":"Camilo Hidalgo", "color": "red", "id":"555", "description":"4"}'>Camilo Hidalgo: <span id="555"></span></div>--}}


        @foreach ($array as $key => $specialty)
          <small><b>{{str_replace('_', ' ', $key)}}</b></small>
          <hr />
          @foreach ($specialty as $key2 => $doc)
              <small>{{$key2}}</small>
              @foreach ($doc as $key3 => $activity)
                  @if($activity->specialty_id!=null)
                      <div data-id="{{$activity->user_id . "_" . $activity->activity_id}}" class='fc-event' style="background-color: #{{$activity->color}};" data-color='#{{$activity->color}}' data-event='{"title":"{{$activity->activity->activity_name}}",
                                                                                                                                   "color": "#{{$activity->color}}",
                                                                                                                                   "id":"{{$activity->user_id . "_" . $activity->activity_id}}",
                                                                                                                                   "specialty_id":"{{$activity->specialty_id}}"}'>
                          <small>{{$activity->activity->activity_name}}: <span id="{{$activity->user_id . "_" . $activity->activity_id}}"></span> ({{$activity->assigned_hour}})</small>
                      </div>
                  @else
                      <div data-id="{{$activity->user_id . "_" . $activity->activity_id}}" class='fc-event' style="background-color: #{{$activity->color}};" data-color='#{{$activity->color}}' data-event='{"title":"{{$activity->activity->activity_name}}",
                                                                                                                                   "color": "#{{$activity->color}}",
                                                                                                                                   "id":"{{$activity->user_id . "_" . $activity->activity_id}}",
                                                                                                                                   "profession_id":"{{$activity->profession_id}}"}'>
                          <small>{{$activity->activity->activity_name}}: <span id="{{$activity->user_id . "_" . $activity->activity_id}}"></span> ({{$activity->assigned_hour}})</small>
                      </div>
                  @endif
              @endforeach
          @endforeach
        @endforeach

        {{-- Se agregan vacaciones, y dias del contrato --}}
        {{-- @if($request->rut != 0)
          @foreach ($rrhhs as $key => $rrhh)
            @foreach ($rrhh->contracts as $key2 => $contract)
              {{$contract}}
            @endforeach
          @endforeach
        @endif --}}
      </div>

      <br><hr>

      <h4>Subtotales</h4>
      @foreach ($array as $key => $specialty)
        <small>{{str_replace('_', ' ', $key)}}</small>
        <div>
          <div><small>Hrs.Contratadas: <b><span id="total_contratadas_{{$key}}"></span></b></small></div>
          <div><small>Hrs.Disponibles: <b><span id="total_disponibles_{{$key}}"></span></b></small></div>
        </div>
        <br />
      @endforeach
      <br>

    </div>


    <div id='calendar'></div>

    <div style='clear:both'></div>

  </div>

    @canany(['administrador'])
        <br /><hr />
        <div style="height: 300px; overflow-y: scroll;">
            <h4 class="mt-3">Historial de cambios</h4>

            {{-- programaciones --}}
            @foreach ($calendarProgrammings as $key => $calendarProgramming)
                @if ($calendarProgramming->specialty_id!=null)
                    <table class="table table-sm small text-muted mt-3">
                        <thead><tr class="table-primary"><th>{{$calendarProgramming->specialty->specialty_name}} - {{$calendarProgramming->user->OfficialFullName}}</th></tr></thead>
                    </table>
                @else
                    <table class="table table-sm small text-muted mt-3">
                        <thead><tr class="table-primary"><th>{{$calendarProgramming->profession->profession_name}} - {{$calendarProgramming->user->OfficialFullName}}</th></tr></thead>
                    </table>
                @endif

                @include('partials.audit_loop', ['audits' => $calendarProgramming->audits] )
            @endforeach

            {{-- programaciones eliminados --}}
            @foreach ($calendarProgrammingsDeleted as $key => $calendarProgrammingDeleted)
                @if ($calendarProgrammingDeleted->activity)
                    <table class="table table-sm small text-muted mt-3">
                        <thead><tr class="table-danger"><th>{{$calendarProgrammingDeleted->activity->activity_name}} - {{$calendarProgrammingDeleted->user->OfficialFullName}}</th></tr></thead>
                    </table>
                @endif
                @include('partials.audit_loop', ['audits' => $calendarProgrammingDeleted->audits] )
            @endforeach

        </div>
    @endcanany

    <div id="page-loader" style="display: none">
      <span class="preloader-interior"></span>
    </div>

  @endsection

  @section('custom_js')



  <script src='{{asset('assets/fullcalendar/packages/core/main.js')}}'></script>
  <script src='{{asset('assets/fullcalendar/packages/interaction/main.js')}}'></script>
  <script src='{{asset('assets/fullcalendar/packages/daygrid/main.js')}}'></script>
  <script src='{{asset('assets/fullcalendar/packages/timegrid/main.js')}}'></script>
  <script src='{{asset('assets/fullcalendar/packages/list/main.js')}}'></script>

  <script src='{{asset('assets/fullcalendar/packages-premium/resource-common/main.js')}}'></script>
  <script src='{{asset('assets/fullcalendar/packages-premium/resource-daygrid/main.js')}}'></script>
  <script src='{{asset('assets/fullcalendar/packages-premium/resource-timegrid/main.js')}}'></script>

  <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>

  {{-- <script src='{{asset('js/jquery-ui.min.js')}}'></script> --}}

  <style>
  #external-events {
      float: left;
      width: 180px;
      padding: 0 5px;
      border: 1px solid #ccc;
      background: #eee;
      text-align: left;
  }
  </style>

  <script>

  // ################# Inicalización de datos
  @foreach ($array as $key => $specialty)
    @foreach ($specialty as $key2 => $doc)

        @foreach ($doc as $key3 => $theo)

            // ciclo para obtener totales por profesional segun eventos guardados en bd
            var cont_eventos_bd = 0;
            @foreach ($calendarProgrammings as $key3 => $calendarProgramming)
              @if($theo->user_id . "_" . $theo->activity_id == $calendarProgramming->user_id . "_" . $calendarProgramming->activity_id)
                cont_eventos_bd+= {{$calendarProgramming->duration_calendar_programming}};
              @endif
            @endforeach

            var bolsa_{{$theo->user_id . "_" . $theo->activity_id}} = {{$theo->assigned_hour}} - cont_eventos_bd;

        @endforeach


    @endforeach
  @endforeach


  $(document).ready(function(){

    //obtiene información de bolsas
    var cont = 0;
    var cont_contratado = 0;
    @foreach ($array as $key => $specialty)
      cont = 0;
      cont_contratado = 0;
      @foreach ($specialty as $key2 => $doc)

        @foreach ($doc as $key3 => $theo)

            document.getElementById("{{$theo->user_id . "_" . $theo->activity_id}}").innerHTML = bolsa_{{$theo->user_id . "_" . $theo->activity_id}};
            cont += bolsa_{{$theo->user_id . "_" . $theo->activity_id}};
            cont_contratado += {{$theo->assigned_hour}};

        @endforeach

        document.getElementById("total_disponibles_{{$key}}").innerHTML = cont;
        document.getElementById("total_contratadas_{{$key}}").innerHTML = cont_contratado;

      @endforeach


    @endforeach

  });


  // ############## inicialización de calendario
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var Draggable = FullCalendarInteraction.Draggable

    var containerEl = document.getElementById('external-events-list');
    new Draggable(containerEl, {
      itemSelector: '.fc-event'
    });

    var diff_ = 0;
    var inicio_start;
    var termino_start;
    var operation_room_id_start;
    var calendar = new FullCalendar.Calendar(calendarEl, {
      schedulerLicenseKey: '0404885988-fcs-1582214203',
      plugins: [ 'interaction', 'resourceDayGrid', 'resourceTimeGrid', 'list' ],
      defaultView: 'resourceTimeGridDay',
      editable: true,
      selectable: true,
      eventLimit: true, // allow "more" link when too many events
      displayEventEnd: true,
      allDaySlot: false,
      scrollTime: '08:00',
      firstDay: 1,
      defaultDate: '{{$date}}',
      locale: 'es', // the initial locale
      navLinks: true,
      eventTextColor: 'white',
      header: {
        left: '',//prev,next today
        center: 'title',
        right: 'resourceTimeGridDay,resourceTimeGridTwoDay'//,timeGridWeek,dayGridMonth'
      },
      titleFormat: { // will produce something like "Tuesday, September 18, 2018"
            month: 'long',
            year: 'numeric',
            day: 'numeric',
            weekday: 'long'
        },
      // eventBackgroundColor:'#ff0000',

      // views: {
      //   resourceTimeGridTwoDay: {
      //     type: 'resourceTimeGrid',
      //     duration: { days: 2 },
      //     buttonText: '2 days',
      //   }
      // },

      resources: [
        @foreach ($operatingRooms as $key => $operatingRoom)
          { id: '{{$operatingRoom->id}}', title: '{{$operatingRoom->name}}' },
        @endforeach
        // { id: '1', title: 'Pabellón 1' },
      ],

      events: [

        @foreach ($calendarProgrammings as $key => $calendarProgramming)
            //se imprimen horas de profesiones
            @if($calendarProgramming->specialty != null)
                { id: '{{$calendarProgramming->user_id . "_" . $calendarProgramming->activity_id}}', title: '{{$calendarProgramming->user->OfficialFullName . " - " . $calendarProgramming->activity->activity_name}}',
                  color:'#{{$calendarProgramming->specialty->color}}', resourceId: '{{$calendarProgramming->operating_room_id}}',
                  start: '{{$calendarProgramming->start_date}}', end: '{{$calendarProgramming->end_date}}',
                  specialty_id: '{{$calendarProgramming->specialty_id}}'},
            @else
            //se imprimen horas de especialistas
                { id: '{{$calendarProgramming->user_id . "_" . $calendarProgramming->activity_id}}', title: '{{$calendarProgramming->user->OfficialFullName . " - " . $calendarProgramming->activity->activity_name}}',
                  color:'#{{$calendarProgramming->profession->color}}', resourceId: '{{$calendarProgramming->operating_room_id}}',
                  start: '{{$calendarProgramming->start_date}}', end: '{{$calendarProgramming->end_date}}',
                  profession_id: '{{$calendarProgramming->profession_id}}'},
            @endif
        @endforeach

        //Solo si es que se selecciona un profesional, se cargan sus teoricos.
        @if($request->rut != 0)
          @foreach ($theoreticalProgrammings as $key => $theoreticalProgramming)
            @if($theoreticalProgramming->user_id == $request->rut)
                { id:99999, title: 'teorico', rendering: 'background', //overlap: false,
                  start: '{{$theoreticalProgramming->start_date}}', end: '{{$theoreticalProgramming->end_date}}'},
            @endif
          @endforeach
        @endif

        // Solo si es que se selecciona un profesional, se cargan sus dias de contrato (feriados, etc).
        @if($request->rut != 0)
          @foreach ($contract_days as $key => $contract_day)
            @if($contract_day->user_id == $request->rut)
                { id:99999, title: 'Administrativos', rendering: 'background', //overlap: false,
                  start: '{{$contract_day->start_date}}', end: '{{$contract_day->end_date}}'},
            @endif
          @endforeach
        @endif

      ],

      navLinkDayClick: function(date, jsEvent) {
        console.log('day', date.toISOString());
        console.log('coords', jsEvent.pageX, jsEvent.pageY);
      },

      // Recepción de eventos
      eventReceive: function(info) {
          // console.log(calendar.component);
        var fecha_inicio = info.event.start;
        info.event.setEnd(add_minutes(fecha_inicio,60));
        var fecha_termino = info.event.end;

        //verifica que no exista un evento en la misma hora en otro pabellón
        var flag = 0;
        var events = calendar.getEvents();
        console.log(events);
        events.forEach(function(element){
            //se verifica que no sea teorico ni administrativo
            if(!(element.id == 99999 || element.id == 99998)){
                //se verifica que sea la misma persona
                if (element.id == info.event.id.toString()) {
                    // alert(formatDateWithHour2(element.start) +"<br />"+ formatDateWithHour2(fecha_inicio) +"<br />"+ formatDateWithHour2(element.end) +"<br />"+ formatDateWithHour2(fecha_termino));
                    //se excluye el último evento (se consiera en todos los eventos del calendario)
                    if (formatDateWithHour2(element.start) != formatDateWithHour2(fecha_inicio) && formatDateWithHour2(element.end) != formatDateWithHour2(fecha_termino)) {
                    // if(info.event.start != "1900-01-01 00:00"){
                        if ((formatDateWithHour2(fecha_inicio) >= formatDateWithHour2(element.start) && formatDateWithHour2(fecha_inicio) < formatDateWithHour2(element.end)) ||
                            (formatDateWithHour2(fecha_termino) > formatDateWithHour2(element.start) && formatDateWithHour2(fecha_termino) <= formatDateWithHour2(element.end)) ||
                            (formatDateWithHour2(fecha_inicio) < formatDateWithHour2(element.start) && formatDateWithHour2(fecha_termino) > formatDateWithHour2(element.end))) {
                            alert("Ya existe un evento en esa hora para el profesional.");
                            info.event.remove();
                            flag = 1;
                            return;
                        }
                    }
                }
            }
        });

        //validacion: no sigue y no guarda evento
        if (flag == 1) {return;}

        //verifica cantidad  bolsa semanal
        //realiza el descuento
        @foreach ($array as $key => $specialty)
          cont = 0;
          @foreach ($specialty as $key2 => $doc)

            @foreach ($doc as $key3 => $theo)

                if(info.event.id == "{{$theo->user_id . "_" . $theo->activity_id}}"){
                  if((bolsa_{{$theo->user_id . "_" . $theo->activity_id}} - 1) < 0){alert("Excedió horas semanales contratas.");info.event.remove();return;} //revierte si se llega a cero
                  document.getElementById("{{$theo->user_id . "_" . $theo->activity_id}}").innerHTML = (bolsa_{{$theo->user_id . "_" . $theo->activity_id}} - 1);
                  bolsa_{{$theo->user_id . "_" . $theo->activity_id}} = bolsa_{{$theo->user_id . "_" . $theo->activity_id}} - 1;
                }
                cont += bolsa_{{$theo->user_id . "_" . $theo->activity_id}};

            @endforeach

          @endforeach
          document.getElementById("total_disponibles_{{$key}}").innerHTML = cont;
        @endforeach

        //guarda evento
        console.log(info.event);
        saveMyData(info.event);

        // Elimina eventos background
        var events = calendar.getEvents();
        events.forEach(function(element){
          if(element.id == 99999 || element.id == 99998){
             element.remove();
          }
        });

    },

    //######### desplazamiento de eventos
    eventDragStart: function(info) {
        // Elimina eventos
        var events = calendar.getEvents();
        events.forEach(function(element){
          if(element.id == 99999 || element.id == 99998){
             element.remove();
          }
        });

        //eventos teóricos
        var id = info.event.id;
        @foreach ($theoreticalProgrammings as $key => $theoreticalProgramming)
          if({{$theoreticalProgramming->user_id . "_" . $theoreticalProgramming->activity_id}} == id){
              var event={id:99999, title: 'teorico', rendering: 'background', //overlap: false,
                        start: '{{$theoreticalProgramming->start_date}}', end: '{{$theoreticalProgramming->end_date}}'};
              calendar.addEvent(event);
          }
        @endforeach

        //eventos días administrativos
        @foreach ($contract_days as $key => $contract_day)
            if({{$contract_day->user_id . "_" . $theoreticalProgramming->activity_id}} == id){
                var event={id:99999, title: 'Administrativo', rendering: 'background', //overlap: false,
                          start: '{{$contract_day->start_date}}', end: '{{$contract_day->end_date}}'};
                calendar.addEvent(event);
            }
        @endforeach

        console.log(info);
        inicio_start = info.event.start;
        termino_start = info.event.end;
        operation_room_id_start = info.event.getResources().map(function(resource) { return resource.id }).toString();
        // deleteMyDataForce(info.event);
    },

    eventDrop: function(info) {
        // Elimina eventos background
        var events = calendar.getEvents();
        events.forEach(function(element){
          if(element.id == 99999 || element.id == 99998){
             element.remove();
          }
        });

        //verifica que no exista un evento en la misma hora en otro pabellón
        var flag = 0;
        var fecha_inicio = info.event.start;
        var fecha_termino = info.event.end;
        var events = calendar.getEvents();
        events.forEach(function(element){
            //se verifica que no sea teorico ni administrativo
            if(!(element.id == 99999 || element.id == 99998)){
                //se verifica que sea la misma persona
                if (element.id == info.event.id.toString()) {
                    //se excluye el último evento (se consiera en todos los eventos del calendario)
                    if (formatDateWithHour2(element.start) != formatDateWithHour2(fecha_inicio) && formatDateWithHour2(element.end) != formatDateWithHour2(fecha_termino)) {
                        if ((formatDateWithHour2(fecha_inicio) >= formatDateWithHour2(element.start) && formatDateWithHour2(fecha_inicio) < formatDateWithHour2(element.end)) ||
                            (formatDateWithHour2(fecha_termino) > formatDateWithHour2(element.start) && formatDateWithHour2(fecha_termino) <= formatDateWithHour2(element.end)) ||
                            (formatDateWithHour2(fecha_inicio) < formatDateWithHour2(element.start) && formatDateWithHour2(fecha_termino) > formatDateWithHour2(element.end))) {
                                alert("Ya existe un evento en esa hora para el profesional.");
                                info.event.setStart(inicio_start);
                                info.event.setEnd(termino_start);
                                info.event.setResources(operation_room_id_start);
                                flag = 1;
                                return;
                        }
                    }
                }
            }
        });

        //validacion
        if (flag == 1) {return;}

        // saveMyData(info.event);
        updateMyData(info.event);
      },

      // drop: function(date, jsEvent, ui) {
      //     $(ui.helper).remove();
      //     $(ui.draggable).remove();
      // },

      // drop: function(date, jsEvent, ui) {
      //     $(ui.helper).remove();
      //     $(ui.draggable).remove();
      // },

      eventDragStop: function(info) {
          if(isEventOverDiv(info.jsEvent.clientX, info.jsEvent.clientY)) {
            var inicio = info.event.start;
            var termino = info.event.end;
            var diff =(termino.getTime() - inicio.getTime()) / 1000;
            diff /= 60;
            diff_ = diff/60;
            //alert(diff_);
            //console.log(info.event);

            @foreach ($array as $key => $specialty)
              cont = 0;
              @foreach ($specialty as $key2 => $doc)

                @foreach ($doc as $key3 => $activity)

                    if(info.event.id == "{{$activity->user_id . "_" . $activity->activity_id}}"){
                      if((bolsa_{{$activity->user_id . "_" . $activity->activity_id}} - + diff_) < 0){alert("Excedió horas semanales contratas.");info.event.revert();return;}
                      document.getElementById("{{$activity->user_id . "_" . $activity->activity_id}}").innerHTML = (bolsa_{{$activity->user_id . "_" . $activity->activity_id}} + diff_);
                      bolsa_{{$activity->user_id . "_" . $activity->activity_id}} = bolsa_{{$activity->user_id . "_" . $activity->activity_id}} + diff_;
                    }
                    cont += bolsa_{{$activity->user_id . "_" . $activity->activity_id}};

                @endforeach

              @endforeach
              document.getElementById("total_disponibles_{{$key}}").innerHTML = cont;
            @endforeach

            info.event.remove();
            deleteMyData(info.event);
          }
      },

      // eventRender: function(event, element) {
      //   element.append( "<span class='removebtn'>X</span>" );
      //   element.find(".removebtn").click(function() {
      //     $('#calendar').fullCalendar('removeEvents',event._id);
      //   });
      // },

      eventClick: function(info) {
        info.jsEvent.preventDefault(); // don't let the browser navigate

        console.log(info.event);
        if (info.event.id) {
            var event = calendar.getEventById(info.event.id);

            if(confirm(info.event.title + "\n" + formatDateWithHour(info.event.start) + " - " + formatDateWithHour(info.event.end) + "\n" + "\n" + "¿Desea eliminar la hora?")){
                var inicio = info.event.start;
                var termino = info.event.end;
                var diff =(termino.getTime() - inicio.getTime()) / 1000;
                diff /= 60;
                diff_ = diff/60;
                //alert(diff_);
                //console.log(info.event);

                @foreach ($array as $key => $specialty)
                  cont = 0;
                  @foreach ($specialty as $key2 => $doc)

                    @foreach ($doc as $key3 => $activity)

                        if(info.event.id == "{{$activity->user_id . "_" . $activity->activity_id}}"){
                          document.getElementById("{{$activity->user_id . "_" . $activity->activity_id}}").innerHTML = (bolsa_{{$activity->user_id . "_" . $activity->activity_id}} + diff_);
                          bolsa_{{$activity->user_id . "_" . $activity->activity_id}} = bolsa_{{$activity->user_id . "_" . $activity->activity_id}} + diff_;
                        }
                        cont += bolsa_{{$activity->user_id . "_" . $activity->activity_id}};

                    @endforeach

                  @endforeach
                  document.getElementById("total_disponibles_{{$key}}").innerHTML = cont;
                @endforeach

                info.event.remove();
                deleteMyData(info.event);
            }
        }
      },

      //######## redimención de eventos
      eventResizeStart: function(info){
        var inicio = info.event.start;
        var termino = info.event.end;
        var diff =(termino.getTime() - inicio.getTime()) / 1000;
        diff /= 60;
        diff_ = diff/60;

        // Elimina eventos
        var events = calendar.getEvents();
        events.forEach(function(element){
          if(element.id == 99999 || element.id == 99998){
             element.remove();
          }
        });

        //eventos teóricos
        var rut = info.event.id;
        @foreach ($theoreticalProgrammings as $key => $theoreticalProgramming)
          if({{$theoreticalProgramming->user_id}} == rut){
              var event={id:99999, title: 'teorico', rendering: 'background', //overlap: false,
                        start: '{{$theoreticalProgramming->start_date}}', end: '{{$theoreticalProgramming->end_date}}'};
              calendar.addEvent(event);
          }
        @endforeach

        //eventos días administrativos
        @foreach ($contract_days as $key => $contract_day)
            if({{$contract_day->user_id}} == rut){
                var event={id:99999, title: 'Administrativo', rendering: 'background', //overlap: false,
                          start: '{{$contract_day->start_date}}', end: '{{$contract_day->end_date}}'};
                calendar.addEvent(event);
            }
        @endforeach

        // console.log(info.event);
        // deleteMyDataForce(info.event);
        inicio_start = info.event.start;
        termino_start = info.event.end;
        operation_room_id_start = info.event.getResources().map(function(resource) { return resource.id }).toString();
      },

      eventResize: function(info) {
        var fecha_inicio = info.event.start;
        var fecha_termino = info.event.end;
        var diff =(fecha_termino.getTime() - fecha_inicio.getTime()) / 1000;
        diff /= 60;
        diff = (diff/60) - diff_;

        //verifica que no exista un evento en la misma hora en otro pabellón
        var flag = 0;
        var events = calendar.getEvents();
        events.forEach(function(element){
            //se verifica que no sea teorico ni administrativo
            if(!(element.id == 99999 || element.id == 99998)){
                //se verifica que sea la misma persona
                if (element.id == info.event.id.toString()) {
                    //se excluye el último evento (se consiera en todos los eventos del calendario)
                    if (formatDateWithHour2(element.start) != formatDateWithHour2(fecha_inicio) && formatDateWithHour2(element.end) != formatDateWithHour2(fecha_termino)) {
                        //validacion
                        if ((formatDateWithHour2(fecha_inicio) >= formatDateWithHour2(element.start) && formatDateWithHour2(fecha_inicio) < formatDateWithHour2(element.end)) ||
                            (formatDateWithHour2(fecha_termino) > formatDateWithHour2(element.start) && formatDateWithHour2(fecha_termino) <= formatDateWithHour2(element.end)) ||
                            (formatDateWithHour2(fecha_inicio) < formatDateWithHour2(element.start) && formatDateWithHour2(fecha_termino) > formatDateWithHour2(element.end))) {
                                alert("Existe un evento en para el profesional en la hora que intenta modificar.");
                                info.event.setStart(inicio_start);
                                info.event.setEnd(termino_start);
                                info.event.setResources(operation_room_id_start);
                                flag = 1;
                                return;
                        }
                    }
                }
            }
        });

        //validacion
        if (flag == 1) {return;}

        //verifica y obtiene bolsas
        @foreach ($array as $key => $specialty)
          cont = 0;
          @foreach ($specialty as $key2 => $doc)

            @foreach ($doc as $key3 => $activity)

                if(info.event.id == "{{$activity->user_id . "_" . $activity->activity_id}}"){
                  if((bolsa_{{$activity->user_id . "_" . $activity->activity_id}} - diff) < 0){alert("Excedió horas semanales contratas.");info.revert();return;} //revierte si se llega a cero
                  document.getElementById("{{$activity->user_id . "_" . $activity->activity_id}}").innerHTML = (bolsa_{{$activity->user_id . "_" . $activity->activity_id}} - diff);
                  bolsa_{{$activity->user_id . "_" . $activity->activity_id}} = bolsa_{{$activity->user_id . "_" . $activity->activity_id}} - diff;
                }
                cont += bolsa_{{$activity->user_id . "_" . $activity->activity_id}};

            @endforeach

          @endforeach
          document.getElementById("total_disponibles_{{$key}}").innerHTML = cont;
        @endforeach

        // Elimina eventos background
        var events = calendar.getEvents();
        events.forEach(function(element){
          if(element.id == 99999 || element.id == 99998){
             element.remove();
          }
        });

        // if(info.event.id == "111"){
        //   if((bolsa_111 - diff) < 0){alert("Excedió horas semanales contratas.");info.revert();return;} //revierte si se llega a cero
        //   document.getElementById("111").innerHTML = (bolsa_111 - diff);
        //   bolsa_111 = bolsa_111 - diff;
        //   document.getElementById("total_traumatologia").innerHTML = bolsa_111 + bolsa_222 + bolsa_333;
        // }
        console.log(info.event);
        // saveMyData(info.event);
        updateMyData(info.event);
      }




    });

    // al iniciar presionar en eventos externos carga theoretical programmer calendar
    $(".fc-event").mousedown(function(event) {
        // Elimina eventos
        var events = calendar.getEvents();
        events.forEach(function(element){
          if(element.id == 99999 || element.id == 99998){
             element.remove();
          }
        });

        //insert eventos background

        //eventos teóricos
        var rut = $(this).attr('data-id');
        @foreach ($theoreticalProgrammings as $key => $theoreticalProgramming)
          if('{{$theoreticalProgramming->user_id . "_" . $theoreticalProgramming->activity_id}}' == rut){
              @foreach ($OperatingRoomProgrammings as $key => $OperatingRoomProgramming)
                @if(date('d', strtotime($OperatingRoomProgramming->start_date)) == date('d', strtotime($theoreticalProgramming->start_date)) &&
                    $theoreticalProgramming->specialty_id == $OperatingRoomProgramming->specialty_id)
                    var event={id:99999, title: 'teorico', rendering: 'background', //overlap: false,
                               resourceId: '{{$OperatingRoomProgramming->operating_room_id}}',
                               start: '{{$theoreticalProgramming->start_date}}', end: '{{$theoreticalProgramming->end_date}}'};
                      console.log(event);
                    calendar.addEvent(event);
                @endif
              @endforeach

          }
        @endforeach

        //eventos días administrativos
        @foreach ($contract_days as $key => $contract_day)
            if({{$contract_day->user_id}} == rut){
                var event={id:99999, title: 'Administrativo', rendering: 'background', //overlap: false,
                          start: '{{$contract_day->start_date}}', end: '{{$contract_day->end_date}}'};
                calendar.addEvent(event);
            }
        @endforeach

    });

    // $( ".fc-event" ).click(function() {
    //
    //     // Elimina eventos
    //     var events = calendar.getEvents();
    //     events.forEach(function(element){
    //       if(element.id == 99999){
    //          element.remove();
    //       }
    //     });
    //
    //     //insert eventos background
    //     var rut = $(this).attr('data-id');
    //     @foreach ($theoreticalProgrammings as $key => $theoreticalProgramming)
    //       if({{$theoreticalProgramming->user_id}} == rut){
    //         @foreach ($operatingRooms as $key => $operatingRoom)
    //           var event={id:99999, title: 'Teórico', resourceId: '{{$operatingRoom->id}}', rendering: 'background',
    //                     start: '{{$theoreticalProgramming->start_date}}', end: '{{$theoreticalProgramming->end_date}}'};
    //           calendar.addEvent(event);
    //         @endforeach
    //       }
    //     @endforeach
    // });

    function saveMyData(event) {

      let event_start = new Date(event.start)
      let start_date = event_start.getFullYear() + "-" + (event_start.getMonth() + 1) + "-" + event_start.getDate() + " " + event_start.getHours() + ":" + event_start.getMinutes()
      let event_end = new Date(event.end)
      let end_date = event_end.getFullYear() + "-" + (event_end.getMonth() + 1) + "-" + event_end.getDate() + " " + event_end.getHours() + ":" + event_end.getMinutes()
      let operating_room_id = event.getResources().map(function(resource) { return resource.id }).toString();
      let rut = event.id.toString().split("_")[0];
      let activity_id = event.id.toString().split("_")[1];

      //obtiene id de especialidad
      var specialty_id;
      if (typeof event.extendedProps.specialty_id === 'undefined') {
          specialty_id = null;
      }else{
          specialty_id = event.extendedProps.specialty_id.toString();
      }
      //obtiene id de profession
      let profession_id;
      if (typeof event.extendedProps.profession_id === 'undefined') {
          profession_id = null;
      }else{
          profession_id = event.extendedProps.profession_id.toString();
      }

      console.log(rut,specialty_id,profession_id,operating_room_id,start_date,end_date,activity_id);

      $.ajax({
          url: "{{ route('medical_programmer.calendar_programming.saveMyEvent') }}",
          type: 'post',
          data:{user_id:rut,specialty_id:specialty_id,profession_id:profession_id,activity_id:activity_id,operating_room_id:operating_room_id,start_date:start_date,end_date:end_date},
          headers: {
              'X-CSRF-TOKEN': "{{ csrf_token() }}"
          },
          // success:function(){
          //     alert("succes drag");
          // },error:function(){
          //     alert("erreur drag !!!!");
          // }
      });
    }

    function updateMyData(event) {
      let start_date = formatDateWithHour(event.start);
      let start_date_start = formatDateWithHour(inicio_start);

      let end_date = formatDateWithHour(event.end);
      let end_date_start = formatDateWithHour(termino_start);

      let operating_room_id = event.getResources().map(function(resource) { return resource.id }).toString();
      let rut = event.id.toString();

      //obtiene id de especialidad
      let specialty_id;
      if (typeof event.extendedProps.specialty_id === 'undefined') {
          specialty_id = null;
      }else{
          specialty_id = event.extendedProps.specialty_id.toString();

      }
      //obtiene id de profession
      let profession_id;
      if (typeof event.extendedProps.profession_id === 'undefined') {
          profession_id = null;
      }else{
          profession_id = event.extendedProps.profession_id.toString();
      }

      console.log(rut + " " + operation_room_id_start + " " + operating_room_id + " F1" + start_date_start + " F1'" + start_date + " F2" + end_date_start + " F2'"  + end_date + " " + specialty_id);

      $.ajax({
          url: "{{ route('medical_programmer.calendar_programming.updateMyEvent') }}",
          type: 'post',
          data:{rut:rut,operation_room_id_start:operation_room_id_start, operating_room_id:operating_room_id,specialty_id:specialty_id,profession_id:profession_id,start_date_start:start_date_start, start_date:start_date,end_date_start:end_date_start, end_date:end_date},
          headers: {
              'X-CSRF-TOKEN': "{{ csrf_token() }}"
          },
      });
    }

    function deleteMyData(event) {

      let event_start = new Date(event.start)
      let start_date = event_start.getFullYear() + "-" + (event_start.getMonth() + 1) + "-" + event_start.getDate() + " " + event_start.getHours() + ":" + event_start.getMinutes()
      let event_end = new Date(event.end)
      let end_date = event_end.getFullYear() + "-" + (event_end.getMonth() + 1) + "-" + event_end.getDate() + " " + event_end.getHours() + ":" + event_end.getMinutes()
      let operating_room_id = event.getResources().map(function(resource) { return resource.id }).toString();
      let rut = event.id.toString();

      //obtiene id de especialidad
      let specialty_id;
      if (typeof event.extendedProps.specialty_id === 'undefined') {
          specialty_id = null;
      }else{
          specialty_id = event.extendedProps.specialty_id.toString();
      }
      //obtiene id de profession
      let profession_id;
      if (typeof event.extendedProps.profession_id === 'undefined') {
          profession_id = null;
      }else{
          profession_id = event.extendedProps.profession_id.toString();
      }

      $.ajax({
          url: "{{ route('medical_programmer.calendar_programming.deleteMyEvent') }}",
          type: 'post',
          data:{rut:rut,specialty_id:specialty_id,profession_id:profession_id,operating_room_id:operating_room_id,start_date:start_date,end_date:end_date},
          headers: {
              'X-CSRF-TOKEN': "{{ csrf_token() }}"
          },
          // success:function(){
          //     alert("succes drag");
          // },error:function(){
          //     alert("erreur drag !!!!");
          // }
      });
    }

    function deleteMyDataForce(event) {

      let event_start = new Date(event.start)
      let start_date = event_start.getFullYear() + "-" + (event_start.getMonth() + 1) + "-" + event_start.getDate() + " " + event_start.getHours() + ":" + event_start.getMinutes()
      let event_end = new Date(event.end)
      let end_date = event_end.getFullYear() + "-" + (event_end.getMonth() + 1) + "-" + event_end.getDate() + " " + event_end.getHours() + ":" + event_end.getMinutes()
      let operating_room_id = event.getResources().map(function(resource) { return resource.id }).toString();
      let rut = event.id.toString();

      //obtiene id de especialidad
      let specialty_id;
      if (typeof event.extendedProps.specialty_id === 'undefined') {
          specialty_id = null;
      }else{
          specialty_id = event.extendedProps.specialty_id.toString();
      }
      //obtiene id de profession
      let profession_id;
      if (typeof event.extendedProps.profession_id === 'undefined') {
          profession_id = null;
      }else{
          profession_id = event.extendedProps.profession_id.toString();
      }

      $.ajax({
          url: "{{ route('medical_programmer.calendar_programming.deleteMyEventForce') }}",
          type: 'post',
          data:{rut:rut,specialty_id:specialty_id,profession_id:profession_id,operating_room_id:operating_room_id,start_date:start_date,end_date:end_date},
          headers: {
              'X-CSRF-TOKEN': "{{ csrf_token() }}"
          },
          // success:function(){
          //     alert("succes drag");
          // },error:function(){
          //     alert("erreur drag !!!!");
          // }
      });
    }

    var isEventOverDiv = function(x, y) {
        var external_events = $( '#external-events' );
        var offset = external_events.offset();
        offset.right = external_events.width() + offset.left;
        offset.bottom = external_events.height() + offset.top;

        // Compare
        if (x >= offset.left
            && y >= offset.top
            && x <= offset.right
            && y <= offset .bottom) { return true; }
        return false;
    }

    var add_minutes =  function (dt, minutes) {
        return new Date(dt.getTime() + minutes*60000);
    }

    calendar.setOption('locale', 'es');
    calendar.render();

    //obtenemos semana traida desde bd (con esa info para comparación)
    $('#prev').click(function(e) {
        calendar.prev();
        //console.log(calendar.state.currentDate);

        var calendar_date = formatDate(calendar.state.currentDate);
        var bdweek = {{Carbon\Carbon::parse($date)->format("W")}};
        var calendarweek = semanaISO(calendar_date);

        if (bdweek != calendarweek) {
          // alert("Se ha actualizado la información de la semana.");
          $('#page-loader').fadeIn(500);
          $('#date').val(formatDate2(calendar.state.currentDate));
          $( "#form" ).submit();

        }

    });
    $('#next').click(function(e) {
        calendar.next();
        console.log(calendar.state.currentDate);

        var calendar_date = formatDate(calendar.state.currentDate);
        var bdweek = {{Carbon\Carbon::parse($date)->format("W")}};
        var calendarweek = semanaISO(calendar_date);

        if (bdweek != calendarweek) {
          // alert("Se ha actualizado la información de la semana.");
          $('#page-loader').fadeIn(500);
          $('#date').val(formatDate2(calendar.state.currentDate));
          $( "#form" ).submit();

        }
    });

    //obtiene fecha formateada (se le suma 1 al día para que calce con calendario)
    //formato fecha dd-mm-YYYY
    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + (d.getDate() + 1),
            year = d.getFullYear();

        if (month.length < 2)
            month = '0' + month;
        if (day.length < 2)
            day = '0' + day;
        return [day, month, year].join('-');
    }

    //formatea la fecha YYYY-mm--dd
    function formatDate2(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + (d.getDate() + 1),
            year = d.getFullYear();

        if (month.length < 2)
            month = '0' + month;
        if (day.length < 2)
            day = '0' + day;
        return [year, month, day].join('-');
    }

    function formatDateWithHour(date) {
        var dateStr =
              ("00" + (date.getMonth() + 1)).slice(-2) + "/" +
              ("00" + date.getDate()).slice(-2) + "/" +
              date.getFullYear() + " " +
              ("00" + date.getHours()).slice(-2) + ":" +
              ("00" + date.getMinutes()).slice(-2);
        return dateStr;
    }

    function formatDateWithHour2(date) {
        var dateStr =
              date.getFullYear() + "-" +
              ("00" + (date.getMonth() + 1)).slice(-2) + "-" +
               ("00" + date.getDate()).slice(-2) + " " +
              ("00" + date.getHours()).slice(-2) + ":" +
              ("00" + date.getMinutes()).slice(-2) + ":00";
        return dateStr;
    }

    //obtiene numero de la semana del año
    function semanaISO($fecha){
       if($fecha.match(/\//)){
          $fecha   =   $fecha.replace(/\//g,"-",$fecha); //Permite que se puedan ingresar formatos de fecha ustilizando el "/" o "-" como separador
       };

       $fecha   =   $fecha.split("-"); //Dividimos el string de fecha en trozos (dia,mes,año)
       $dia   =   eval($fecha[0]);
       $mes   =   eval($fecha[1]);
       $ano   =   eval($fecha[2]);

       if ($mes==1 || $mes==2){
          //Cálculos si el mes es Enero o Febrero
          $a   =   $ano-1;
          $b   =   Math.floor($a/4)-Math.floor($a/100)+Math.floor($a/400);
          $c   =   Math.floor(($a-1)/4)-Math.floor(($a-1)/100)+Math.floor(($a-1)/400);
          $s   =   $b-$c;
          $e   =   0;
          $f   =   $dia-1+(31*($mes-1));
       } else {
          //Calculos para los meses entre marzo y Diciembre
          $a   =   $ano;
          $b   =   Math.floor($a/4)-Math.floor($a/100)+Math.floor($a/400);
          $c   =   Math.floor(($a-1)/4)-Math.floor(($a-1)/100)+Math.floor(($a-1)/400);
          $s   =   $b-$c;
          $e   =   $s+1;
          $f   =   $dia+Math.floor(((153*($mes-3))+2)/5)+58+$s;
       };

       //Adicionalmente sumándole 1 a la variable $f se obtiene numero ordinal del dia de la fecha ingresada con referencia al año actual.

       //Estos cálculos se aplican a cualquier mes
       $g   =   ($a+$b)%7;
       $d   =   ($f+$g-$e)%7; //Adicionalmente esta variable nos indica el dia de la semana 0=Lunes, ... , 6=Domingo.
       $n   =   $f+3-$d;

       if ($n<0){
          //Si la variable n es menor a 0 se trata de una semana perteneciente al año anterior
          $semana   =   53-Math.floor(($g-$s)/5);
          $ano      =   $ano-1;
       } else if ($n>(364+$s)) {
          //Si n es mayor a 364 + $s entonces la fecha corresponde a la primera semana del año siguiente.
          $semana   = 1;
          $ano   =   $ano+1;
       } else {
          //En cualquier otro caso es una semana del año actual.
          $semana   =   Math.floor($n/7)+1;
       };

       return $semana; //+"-"+$ano; //La función retorna una cadena de texto indicando la semana y el año correspondiente a la fecha ingresada
    };

  });

  </script>

  @endsection

  @section('custom_js_head')
    <link href='{{asset('assets/fullcalendar/packages/core/main.css')}}' rel='stylesheet' />
    <link href='{{asset('assets/fullcalendar/packages/daygrid/main.css')}}' rel='stylesheet' />
    <link href='{{asset('assets/fullcalendar/packages/timegrid/main.css')}}' rel='stylesheet' />
    <link href='{{asset('assets/fullcalendar/packages/list/main.css')}}' rel='stylesheet' />
    <link href='{{asset('assets/fullcalendar/css/style.css')}}' rel='stylesheet' />
    {{-- <link href='{{asset('css/jquery-ui.min.css')}}' rel='stylesheet' /> --}}
  @endsection
