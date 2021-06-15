@extends('layouts.app')

@section('title', 'agenda')

@section('content')


<form method="POST" class="form-horizontal" action="{{ route('some.open_tprogrammer') }}">
@csrf
@method('POST')

<div class="form-row">

  <div class="form-group col-md-9">
    @livewire('medical_programmer.select-med-prog-employee')
  </div>

  <div class="form-group col-md-3">
    <label for="inputEmail4">&nbsp;</label>
    <button type="submit" class="btn btn-primary form-control">Buscar</button>
  </div>

</div>

</form>


<form method="POST" class="form-horizontal" action="{{ route('some.openAgenda') }}">
@csrf
@method('POST')

<div class="form-row">

  <div class="form-group col-md-6">
    <label for="inputEmail4">&nbsp;</label>
    <!-- <button type="submit" class="btn btn-primary form-control">Buscar</button> -->
  </div>

  <div class="form-group col-md-2">
    <label for="inputEmail4">Desde</label>
    <input type="date" name="from" class="form-control">
  </div>

  <div class="form-group col-md-2">
    <label for="inputEmail4">Hasta</label>
    <input type="date" name="to" class="form-control">
  </div>

  <div class="form-group col-md-2">
    <label for="inputEmail4">&nbsp;</label>
    <button type="submit" class="btn btn-success form-control">Aperturar</button>
  </div>

</div>

</form>



<html lang='en'>
  <head>
    <meta charset='utf-8' />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.7.0/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.7.0/main.min.js'></script>
    <script>

      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            allDaySlot: false,
            firstDay: 1,

            defaultDate: '{{Carbon\Carbon::parse($request->date)->format('d-m-Y')}}',
            slotMinTime: "08:00:00",

            // slotDuration: "00:10:00",
            // slotMaxTime: "17:30:00",
            timeFormat: 'HH:mm',
            locale: 'es',
            slotLabelFormat:
            {
              hour: 'numeric',
              minute: '2-digit',
              omitZeroMinute: false,
              hour12:false,
              meridiem: 'short'
            },

            events: [
              //control 14  de junio
              @if($theoreticalProgrammings)
                @foreach($theoreticalProgrammings as $theoricalProgramming)

                  @if($theoricalProgramming->subactivity)
                      { title: '{{$theoricalProgramming->subactivity->sub_activity_name}}',
                        start: '{{$theoricalProgramming->start_date}}', end: '{{$theoricalProgramming->end_date}}',
                        color:'#F7DC6F'
                      },
                  @else
                      { title: '{{$theoricalProgramming->activity->activity_name}}',
                        start: '{{$theoricalProgramming->start_date}}', end: '{{$theoricalProgramming->end_date}}',
                        color:'#85C1E9'
                      },
                  @endif

                @endforeach
              @endif




            ]
        });

        calendar.render();
      });



    </script>
  </head>
  <body>
    <div id='calendar'></div>
  </body>
</html>
@endsection

@section('custom_js')

@endsection
