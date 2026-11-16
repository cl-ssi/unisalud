@php
    $imports = $this->getImports();
    $running = $imports->contains(fn ($import) => $import->completed_at === null);
@endphp

<x-filament-widgets::widget>
    {{-- Polls only while an upload is running. --}}
    <div @if ($running) wire:poll.3s @endif>
        @if ($imports->isNotEmpty())
            <x-filament::section>
                <div class="space-y-4">
                    @foreach ($imports as $import)
                        @php
                            $total = max((int) $import->total_rows, 0);
                            $processed = min((int) $import->processed_rows, $total);
                            $percent = $total > 0 ? (int) floor($processed * 100 / $total) : 0;
                            $failed = $import->completed_at ? $import->getFailedRowsCount() : null;
                            $done = $import->completed_at !== null;

                            // Remaining time from the pace so far (rows are processed in chunks).
                            $eta = null;
                            if (! $done && $processed > 0) {
                                $elapsed = max(now()->diffInSeconds($import->created_at, true), 1);
                                $seconds = (int) round(($total - $processed) * $elapsed / $processed);
                                $eta = $seconds >= 60 ? 'quedan ~' . ceil($seconds / 60) . ' min' : 'queda menos de 1 min';
                            }
                        @endphp

                        <div>
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 text-sm">
                                <span class="font-medium text-gray-950 dark:text-white">
                                    {{ $done ? 'Importación finalizada' : 'Importando' }}
                                    <span class="font-normal text-gray-500 dark:text-gray-400">· {{ $import->file_name }}</span>
                                </span>
                                <span class="tabular-nums text-gray-500 dark:text-gray-400">
                                    {{ number_format($processed, 0, ',', '.') }} / {{ number_format($total, 0, ',', '.') }} filas
                                    ({{ $percent }}%)@if ($eta) · {{ $eta }}@endif
                                </span>
                            </div>

                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"
                                 role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}">
                                <div @class([
                                        'h-full rounded-full transition-all duration-500',
                                        'bg-primary-600' => ! $done,
                                        'bg-success-600' => $done && ! $failed,
                                        'bg-warning-500' => $done && $failed,
                                     ])
                                     style="width: {{ $percent }}%"></div>
                            </div>

                            @if ($done)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format($import->successful_rows, 0, ',', '.') }} importadas
                                    @if ($failed)
                                        · {{ number_format($failed, 0, ',', '.') }} rechazadas (el archivo con los motivos está en tus notificaciones)
                                    @endif
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-widgets::widget>
