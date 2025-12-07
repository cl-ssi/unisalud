<x-filament::page>

    <div class="flex gap-4 overflow-x-auto pb-2">

        {{-- Total --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-document-text class="w-6 h-6 text-primary-600 mb-1" />
            <div class="text-xs text-gray-500">Total</div>
            <div class="text-xl font-bold">{{ $this->stats['total'] }}</div>
        </div>

        {{-- Primer llamado --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-phone-arrow-up-right class="w-6 h-6 text-blue-600 mb-1" />
            <div class="text-xs text-gray-500">Primer Llamado</div>
            <div class="text-xl font-bold text-blue-600">{{ $this->stats['primer_llamado'] }}</div>
        </div>

        {{-- Segundo llamado --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-phone class="w-6 h-6 text-indigo-600 mb-1" />
            <div class="text-xs text-gray-500">Segundo Llamado</div>
            <div class="text-xl font-bold text-indigo-600">{{ $this->stats['segundo_llamado'] }}</div>
        </div>

        {{-- Citados --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-calendar-days class="w-6 h-6 text-yellow-500 mb-1" />
            <div class="text-xs text-gray-500">Citados</div>
            <div class="text-xl font-bold text-yellow-500">{{ $this->stats['citado'] }}</div>
        </div>

        {{-- Atendidos --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 mb-1" />
            <div class="text-xs text-gray-500">Atendidos</div>
            <div class="text-xl font-bold text-green-600">{{ $this->stats['atendidos'] }}</div>
        </div>

        {{-- Fallecidos --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-x-mark class="w-6 h-6 text-red-700 mb-1" />
            <div class="text-xs text-gray-500">Fallecidos</div>
            <div class="text-xl font-bold text-red-700">{{ $this->stats['fallecidos'] }}</div>
        </div>

    </div>

    {{-- ===================== --}}
{{-- GRÁFICOS CHART.JS --}}
{{-- ===================== --}}

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

    {{-- Gráfico 1: Pacientes por Estado --}}
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Pacientes por Estado</h2>
        <canvas id="chartEstados"></canvas>
    </div>

    {{-- Gráfico 2: Pacientes por Establecimiento --}}
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Pacientes por Establecimiento</h2>
        <canvas id="chartEstablecimientos"></canvas>
    </div>

    {{-- Gráfico 3: Pacientes por Especialidad --}}
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Pacientes por Especialidad</h2>
        <canvas id="chartEspecialidad"></canvas>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const estados = @json($this->chartStats['por_estado']);
    const establecimientos = @json($this->chartStats['por_establecimiento']);
    const especialidades = @json($this->chartStats['por_especialidad']);

    const colors = [
        '#3B82F6', '#EF4444', '#F59E0B', '#10B981',
        '#8B5CF6', '#EC4899', '#14B8A6', '#6366F1'
    ];

    /* ==========
       GRÁFICO 1
       ========== */
    new Chart(document.getElementById('chartEstados'), {
        type: 'bar',
        data: {
            labels: Object.keys(estados),
            datasets: [{
                label: 'Cantidad',
                data: Object.values(estados),
                backgroundColor: colors,
            }]
        },
        options: { responsive: true }
    });

    /* ==========
       GRÁFICO 2
       ========== */
    new Chart(document.getElementById('chartEstablecimientos'), {
        type: 'bar',
        data: {
            labels: Object.keys(establecimientos),
            datasets: [{
                label: 'Cantidad',
                data: Object.values(establecimientos),
                backgroundColor: colors,
            }]
        },
        options: { responsive: true }
    });

    /* ==========
       GRÁFICO 3
       ========== */
    new Chart(document.getElementById('chartEspecialidad'), {
        type: 'bar',
        data: {
            labels: Object.keys(especialidades),
            datasets: [{
                label: 'Cantidad',
                data: Object.values(especialidades),
                backgroundColor: colors,
            }]
        },
        options: { responsive: true }
    });

});
</script>


</x-filament::page>
