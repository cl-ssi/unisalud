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

        {{-- Atendido PRAPS --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 mb-1" />
            <div class="text-xs text-gray-500">Atendido PRAPS</div>
            <div class="text-xl font-bold text-green-600">{{ $this->stats['atendido_praps'] }}</div>
        </div>

        {{-- Atendido HETG/SST/HAH --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-check-circle class="w-6 h-6 text-teal-600 mb-1" />
            <div class="text-xs text-gray-500">Atendido HETG/SST/HAH</div>
            <div class="text-xl font-bold text-teal-600">{{ $this->stats['atendido_hetg_sst_hah'] }}</div>
        </div>

        {{-- Fallecidos --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-x-mark class="w-6 h-6 text-red-700 mb-1" />
            <div class="text-xs text-gray-500">Fallecidos</div>
            <div class="text-xl font-bold text-red-700">{{ $this->stats['fallecidos'] }}</div>
        </div>

        {{-- Egresados --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-arrow-right-on-rectangle class="w-6 h-6 text-orange-600 mb-1" />
            <div class="text-xs text-gray-500">Egresados</div>
            <div class="text-xl font-bold text-orange-600">{{ $this->stats['egresados'] }}</div>
        </div>

        {{-- Sin Estado --}}
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-question-mark-circle class="w-6 h-6 text-gray-400 mb-1" />
            <div class="text-xs text-gray-500">Sin Estado</div>
            <div class="text-xl font-bold text-gray-400">{{ $this->stats['sin_estado'] }}</div>
        </div>

    </div>

    {{-- ===================== --}}
    {{-- LISTA DE ESPERA: abiertos / cerrados, GES y tiempo de espera --}}
    {{-- ===================== --}}
    @php
        $w = $this->waitlistStats;
        $n = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
        $days = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.') . ' días';
    @endphp
    <div class="flex gap-4 overflow-x-auto pb-2 mt-4">
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-folder-open class="w-6 h-6 text-primary-600 mb-1" />
            <div class="text-xs text-gray-500">Abiertos</div>
            <div class="text-xl font-bold text-primary-600">{{ $n($w['abiertos']) }}</div>
        </div>
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-archive-box class="w-6 h-6 text-gray-600 mb-1" />
            <div class="text-xs text-gray-500">Cerrados</div>
            <div class="text-xl font-bold text-gray-700">{{ $n($w['cerrados']) }}</div>
        </div>
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-shield-check class="w-6 h-6 text-green-600 mb-1" />
            <div class="text-xs text-gray-500">GES</div>
            <div class="text-xl font-bold text-green-600">{{ $n($w['ges']) }}</div>
        </div>
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-clipboard-document-check class="w-6 h-6 text-emerald-600 mb-1" />
            <div class="text-xs text-gray-500">Con diagnóstico GES</div>
            <div class="text-xl font-bold text-emerald-600">{{ $n($w['diagnostico_ges']) }}</div>
        </div>
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-clock class="w-6 h-6 text-amber-600 mb-1" />
            <div class="text-xs text-gray-500">Espera promedio (abiertos)</div>
            <div class="text-xl font-bold text-amber-600">{{ $days($w['espera_promedio']) }}</div>
        </div>
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-clock class="w-6 h-6 text-amber-700 mb-1" />
            <div class="text-xs text-gray-500">Espera mediana (abiertos)</div>
            <div class="text-xl font-bold text-amber-700">{{ $days($w['espera_mediana']) }}</div>
        </div>
        <div class="min-w-[180px] p-3 bg-white rounded-lg shadow flex flex-col items-center text-center">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 mb-1" />
            <div class="text-xs text-gray-500">Esperando más de 1 año</div>
            <div class="text-xl font-bold text-red-600">{{ $n($w['mas_de_un_anio']) }}</div>
        </div>
    </div>

    {{-- ===================== --}}
    {{-- DETALLE POR ESTADO --}}
    {{-- ===================== --}}

    <div class="bg-white p-4 rounded-xl shadow mt-8 overflow-x-auto">
        <h2 class="text-lg font-bold mb-3">Detalle por Estado</h2>
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2 pr-4">Estado</th>
                    <th class="py-2 pr-4">Cantidad</th>
                    <th class="py-2 pr-4">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->estadoBreakdown as $row)
                    <tr class="border-b last:border-0">
                        <td class="py-2 pr-4">{{ $row['estado'] }}</td>
                        <td class="py-2 pr-4">{{ $row['cantidad'] }}</td>
                        <td class="py-2 pr-4">{{ $row['porcentaje'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
        <h2 class="text-lg font-bold mb-3">Pacientes por Establecimiento de Destino</h2>
        <canvas id="chartEstablecimientos"></canvas>
    </div>

    {{-- Gráfico 3: Pacientes por Especialidad --}}
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Pacientes por Especialidad (15 principales)</h2>
        <canvas id="chartEspecialidad"></canvas>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Pacientes por Edad</h2>
        <canvas id="chartEdad"></canvas>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Pacientes por Sexo</h2>
        <div class="mx-auto max-w-xs"><canvas id="chartSexo"></canvas></div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Tiempo de Espera (casos abiertos)</h2>
        <canvas id="chartTiempoEspera"></canvas>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Tipo de Prestación</h2>
        <div class="mx-auto max-w-xs"><canvas id="chartTipoPrestacion"></canvas></div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Previsión</h2>
        <canvas id="chartPrevision"></canvas>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Causal de Egreso (casos cerrados)</h2>
        <canvas id="chartCausalEgreso"></canvas>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-3">Comuna (10 principales)</h2>
        <canvas id="chartComuna"></canvas>
    </div>

    <div class="bg-white p-4 rounded-xl shadow md:col-span-2">
        <h2 class="text-lg font-bold mb-3">Ingresos por Año (F_ENTRADA)</h2>
        <canvas id="chartAnioEntrada" height="90"></canvas>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const stats = @json($this->chartStats);

    const colors = [
        '#3B82F6', '#EF4444', '#F59E0B', '#10B981',
        '#8B5CF6', '#EC4899', '#14B8A6', '#6366F1'
    ];

    // One helper for every chart: data is {label: count}.
    function draw(id, data, type = 'bar', options = {}) {
        const el = document.getElementById(id);
        if (! el || ! data || Object.keys(data).length === 0) {
            if (el) el.replaceWith(Object.assign(document.createElement('p'), {
                className: 'text-sm text-gray-500', textContent: 'Sin datos.',
            }));
            return;
        }
        const round = type === 'doughnut';
        new Chart(el, {
            type: type,
            data: {
                labels: Object.keys(data),
                datasets: [{
                    label: 'Pacientes',
                    data: Object.values(data),
                    backgroundColor: colors,
                }],
            },
            options: Object.assign({
                responsive: true,
                plugins: { legend: { display: round } },
            }, options),
        });
    }

    const horizontal = { indexAxis: 'y' };

    draw('chartEstados', stats.por_estado);
    draw('chartEstablecimientos', stats.por_establecimiento, 'bar', horizontal);
    draw('chartEspecialidad', stats.por_especialidad, 'bar', horizontal);
    draw('chartEdad', stats.por_edad);
    draw('chartSexo', stats.por_sexo, 'doughnut');
    draw('chartTiempoEspera', stats.por_tiempo_espera);
    draw('chartTipoPrestacion', stats.por_tipo_prestacion, 'doughnut');
    draw('chartPrevision', stats.por_prevision);
    draw('chartCausalEgreso', stats.por_causal_egreso, 'bar', horizontal);
    draw('chartComuna', stats.por_comuna, 'bar', horizontal);
    draw('chartAnioEntrada', stats.por_anio_entrada);

});
</script>


</x-filament::page>
