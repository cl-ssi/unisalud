<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- LE CNE --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-users" class="h-5 w-5 text-primary-500" />
                    <span>Base de Datos LE CNE</span>
                    @if(!empty($lecneMeta))
                        <x-filament::badge color="success" size="sm">Cargada</x-filament::badge>
                    @else
                        <x-filament::badge color="warning" size="sm">Sin cargar</x-filament::badge>
                    @endif
                </div>
            </x-slot>
            <x-slot name="description">Lista de Espera Consulta Nueva — utilizada para autocompletar datos del paciente al ingresar</x-slot>

            @if(empty($lecneMeta))
                <div class="flex items-center gap-3 rounded-lg bg-warning-50 p-4 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 flex-shrink-0" />
                    <p class="text-sm">La base de datos LE CNE no ha sido cargada. Utilice el botón <strong>Cargar LE CNE</strong> para importar el archivo.</p>
                </div>
            @else
                <dl class="divide-y divide-gray-100 dark:divide-white/5">
                    <div class="flex justify-between py-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Total pacientes</dt>
                        <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($lecneMeta['total']) }}</dd>
                    </div>
                    @if(isset($lecneMeta['nuevos']))
                    <div class="flex justify-between py-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Última importación</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $lecneMeta['nuevos'] }} nuevos · {{ $lecneMeta['actualizados'] }} actualizados
                            @if(($lecneMeta['errores'] ?? 0) > 0)
                                · <span class="text-danger-600">{{ $lecneMeta['errores'] }} errores</span>
                            @endif
                        </dd>
                    </div>
                    @endif
                    <div class="flex justify-between py-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Última carga</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $lecneMeta['fecha'] ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Archivo</dt>
                        <dd class="font-mono text-xs text-gray-600 dark:text-gray-300">{{ $lecneMeta['archivo'] ?? '-' }}</dd>
                    </div>
                </dl>
            @endif
        </x-filament::section>

        {{-- LE Qx --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-5 w-5 text-gray-500" />
                    <span>Base de Datos LE Quirúrgica</span>
                    @if(!empty($leqxMeta))
                        <x-filament::badge color="success" size="sm">Cargada</x-filament::badge>
                    @else
                        <x-filament::badge color="warning" size="sm">Sin cargar</x-filament::badge>
                    @endif
                </div>
            </x-slot>
            <x-slot name="description">Utilizada para detección de duplicados entre especialidades al momento de ingresar un paciente</x-slot>

            @if(empty($leqxMeta))
                <div class="flex items-center gap-3 rounded-lg bg-warning-50 p-4 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 flex-shrink-0" />
                    <p class="text-sm">La base de datos LE Quirúrgica no ha sido cargada. Utilice el botón <strong>Cargar LE Quirúrgica</strong> para importar el archivo.</p>
                </div>
            @else
                <dl class="divide-y divide-gray-100 dark:divide-white/5">
                    <div class="flex justify-between py-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Total registros</dt>
                        <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($leqxMeta['total']) }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Última carga</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $leqxMeta['fecha'] ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Archivo</dt>
                        <dd class="font-mono text-xs text-gray-600 dark:text-gray-300">{{ $leqxMeta['archivo'] ?? '-' }}</dd>
                    </div>
                </dl>
            @endif
        </x-filament::section>

        {{-- Última Exportación LE QX --}}
        <x-filament::section class="lg:col-span-2">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-5 w-5 text-gray-500" />
                    <span>Última Exportación LE QX</span>
                </div>
            </x-slot>
            <x-slot name="description">Registro de la última vez que se descargó el archivo SIGTE desde "Todos los Ingresos"</x-slot>

            @if(empty($lastExport))
                <div class="flex items-center gap-3 rounded-lg bg-warning-50 p-4 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 flex-shrink-0" />
                    <p class="text-sm">Aún no se ha realizado ninguna exportación.</p>
                </div>
            @else
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Fecha</dt>
                        <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $lastExport['fecha'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Descargado por</dt>
                        <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $lastExport['usuario'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Pacientes exportados</dt>
                        <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($lastExport['pacientes']) }}</dd>
                    </div>
                </dl>
            @endif
        </x-filament::section>

    </div>
</x-filament-panels::page>
