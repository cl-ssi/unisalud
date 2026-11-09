<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\On;

abstract class BaseMapWidget extends Widget
{
    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public ?string $baseUrl;

    public ?array $organizations_id = [];

    public ?array $users_id = [];

    public ?array $risks = [];

    public array $patients = [];

    public array $markers = [];

    public function mount(?array $organizations_id = [], ?array $risks = [], ?array $users_id = []): void
    {
        $this->baseUrl          = env('APP_URL', 'https://uni.saludtarapaca.gob.cl/');
        $this->organizations_id = $organizations_id;
        $this->users_id         = $users_id;
        $this->risks            = $risks;
        $this->loadPatients();
    }

    abstract protected function loadPatients(): void;

    #[On('changeFilters')]
    public function changeFilters(?array $organizations_id = [], ?array $users_id = null, ?array $risks = []): void
    {
        $this->organizations_id = $organizations_id ?? $this->organizations_id;
        $this->users_id         = $users_id ?? $this->users_id;
        $this->risks            = $risks ?? $this->risks;
        $this->loadPatients();

        $this->dispatch('markersUpdated', ['markers' => $this->markers]);
    }
}
