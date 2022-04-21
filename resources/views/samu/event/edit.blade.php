@extends('layouts.app')

@section('content')

@include('samu.nav')

<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="mb-3">
            <i class="fas fa-car-crash"></i> Editar cometido {{ $event->id }}
            @if($event->call)
                - Llamada ID: {{ $event->call->id }}
            @else 
                - Sin llamada asociada
            @endif
        </h3>
    </div>
    <div class="col-12 col-md-2 text-right">
        @can('SAMU administrador','SAMU regulador')
            @if($event->status AND !$event->trashed())
            <form method="POST" action="{{ route('samu.event.destroy', $event) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Cometido">
                    <i class="fas fa-trash"></i> Eliminar Cometido
                </button>
            </form>
            @endif
        @endcan
    </div>
</div>

@include('samu.call.partials.associated-calls', ['call' => $event->call])

@if($event->status)
<form method="post" action="{{ route('samu.event.update', $event) }}">
@endif

    @csrf
    @method('PUT')

    @include('samu.event.form', [
        'call'  => null,
        'event' => $event,
        'keys'  => $keys,
        'shift' => $shift,
    ])

    @if($event->status)
        <button type="submit" name="btn_save" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar
        </button>
        
        <button type="submit" name="btn_save_close" id="btn_save_close" class="btn btn-success float-right">
            <i class="fas fa-lock"></i> Guardar y cerrar
        </button>

        <input type="hidden" id="save_close" name="save_close" value="no">
    @else
        @can('SAMU administrador')
            <a class="btn btn-secondary" target="_blank"  href="{{ route('samu.event.report',$event) }}">
                <i class="fas fa-print"></i> Imprimir
            </a>
            @if( $event->created_at->gt(now()->subDays(90)) )
            <a class="btn btn-warning float-right" href="{{ route('samu.event.reopen',$event) }}">
                <i class="fas fa-lock-open"></i> Reabrir < 90 días
            </a>
            @endif
        @endcan
    @endif
    
    <a href="{{ route('samu.event.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    
@if($event->status)    
</form>
@endif

<hr>


@canany(['SAMU'])
    @include('partials.short_audit', ['audits' => $event->audits] )
@endcanany


@endsection

@section('custom_js')
    <script>
        $("#btn_save_close").click(function(event) {
            $('#save_close').val("yes");
        });
    </script>
@endsection
