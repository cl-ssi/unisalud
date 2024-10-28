<div class="timeline">
    @forelse ($events as $event)
        <div class="timeline-item">
            <div class="timeline-icon">
                @if ($event->status === 'derivado')
                    <svg class="w-6 h-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 4a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @elseif ($event->status === 'incontactable')
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="red"> <!-- Cambiar el atributo stroke a rojo -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                @elseif ($event->status === 'egresado')
                    <svg class="w-6 h-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                @else
                    <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>
            <div class="timeline-content">
                <h4 class="font-semibold">{{ \Carbon\Carbon::parse($event->registered_at)->format('d-m-Y H:i:s') }}</h4>
                <p class="text-sm text-gray-600">Estado: <span class="font-medium">{{ ucfirst($event->status) }}</span></p>
                <p class="text-sm text-gray-700">{{ $event->text }}</p>
            </div>
        </div>
    @empty
        <p class="text-gray-500">No hay eventos registrados.</p>
    @endforelse
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 2rem;
        margin-top: 1rem;
        border-left: 2px solid #d1d5db; /* Línea de la timeline */
    }
    .timeline-item {
        display: flex;
        margin-bottom: 1rem;
        position: relative;
    }
    .timeline-icon {
        position: absolute;
        left: -1.25rem;
        background-color: #fff;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        padding: 0.25rem;
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .timeline-content {
        margin-left: 2.5rem;
    }
</style>