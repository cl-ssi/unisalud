<?php

namespace App\Filament\Resources\DependentUserResource\Widgets;

use App\Filament\Resources\DependentUserResource;
use Filament\Widgets\Widget;
use App\Models\DependentUser;
use Livewire\Attributes\On;

class MapWidget extends Widget
{
    // La vista asociada a este Widget
    protected static string $view = 'filament.resources.dependent-user-resource.widgets.map-widget';

    protected int | string | array $columnSpan = 'full';

    protected static bool   $isLazy = false;

    public ?string $baseUrl;
    public ?array $conditions_id = [];
    public ?int $user_id      = null;
    public array $patients    = [];
    public array $markers     = [];

    public function mount(?array $conditions_id = [], ?int $user_id = null): void
    {

        $this->baseUrl          = env('APP_URL', 'https://uni.saludtarapaca.gob.cl/');
        $this->conditions_id    = $conditions_id;        
        $this->user_id          = $user_id;
        $this->loadPatients();
    }

    private function loadPatients(): void
    {
        
        $dependentUsers = DependentUser::has('user.address.location')
            ->with(['user.address.location'])
            ->when($this->conditions_id, function($q) {
                foreach ($this->conditions_id as $condition_id) {
                    $q->whereHas('conditions', fn($qu)  => $qu->where('condition_id', $condition_id));
                }
                return $q;
            })
            ->when($this->user_id, fn($q) => $q->whereHas('user', fn($q) => $q->where('id', $this->user_id)))
            ->get();

        $this->markers = $dependentUsers->map(fn($p) => [
            'id'   => $p->id,
            'url'   => route('filament.admin.resources.dependent-users.view', $p->id),
            'lat'   => $p->user->address->location->latitude,
            'lng'   => $p->user->address->location->longitude,
            'name'    => $p->user->text,
            'address' => $p->user->address->text . ' ' . $p->user->address->line,
        ])->toArray();
    }

    #[On('changeFilters')]
    public function changeFilters(?array $conditions_id = [], ?int $user_id = null): void
    {
        $this->conditions_id = $conditions_id ?? $this->conditions_id;
        $this->user_id      = $user_id      ?? $this->user_id;        
        $this->loadPatients();
    }
}
