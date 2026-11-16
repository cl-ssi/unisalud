<?php

namespace App\Filament\Resources\DemandManagementWaitlistResource\Widgets;

use App\Filament\Imports\DemandManagementWaitlistImporter;
use Filament\Actions\Imports\Models\Import;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

/**
 * Progress of the current user's Gestión Demanda CSV uploads, read from
 * Filament's imports table (updated after every chunk). Polls only while an
 * upload is running; the Import action wakes it up when one starts.
 */
class DemandManagementImportProgress extends Widget
{
    protected static string $view = 'filament.resources.demand-management-waitlist-resource.widgets.import-progress';

    protected int | string | array $columnSpan = 'full';

    /** Finished uploads stay on screen this long. */
    private const SHOW_FINISHED_FOR_MINUTES = 15;

    #[On('demand-management-import-started')]
    public function refreshProgress(): void
    {
        // Re-renders the widget, which picks up the new import.
    }

    public function getImports()
    {
        return Import::query()
            ->where('importer', DemandManagementWaitlistImporter::class)
            ->where('user_id', auth()->id())
            ->where(fn($query) => $query
                ->whereNull('completed_at')
                ->orWhere('completed_at', '>=', now()->subMinutes(self::SHOW_FINISHED_FOR_MINUTES)))
            // An upload whose jobs were lost would otherwise stay on screen forever.
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->limit(3)
            ->get();
    }
}
