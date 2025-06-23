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
    public ?array $organizations_id = [];
    public ?array $users_id      = [];

    public array $patients    = [];
    public array $markers     = [];

    public function mount(?array $conditions_id = [], ?array $users_id = null): void
    {

        $this->baseUrl          = env('APP_URL', 'https://uni.saludtarapaca.gob.cl/');
        $this->conditions_id    = $conditions_id;        
        $this->users_id          = $users_id;
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
            ->when($this->organizations_id, function($q) {
                $q->whereHas('user', function($query) {
                    $query->whereHas('mobileContactPoint', function($query) {
                        $query->whereHas('organization', function($query) {
                            $query->whereIn('id', $this->organizations_id);
                        });
                    });
                });
            })


            ->when($this->users_id, fn($q) => $q->whereHas('user', fn($q) => $q->whereIn('id', $this->users_id))) // Updated to use $this->users_id
            ->get();

        $this->markers = $dependentUsers->map(fn($p) => [
            'id'   => $p->id,
            'url'   => route('filament.admin.resources.dependent-users.view', $p->id),
            'lat'   => $p->user->address->location->latitude,
            'lng'   => $p->user->address->location->longitude,
            'name'    => $p->user->text,
            'address' => $p->user->address->text . ' ' . $p->user->address->line,
            'flooded' => $p->user->address->location->flooded,
            'alluvion' => $p->user->address->location->alluvion,
        ])->toArray();
    }

    #[On('changeFilters')]
    public function changeFilters(?array $conditions_id = [], ?array $organizations_id = [], ?array $users_id = null): void
    {
        $this->conditions_id = $conditions_id ?? $this->conditions_id;
        $this->organizations_id = $organizations_id ?? $this->organizations_id;            
        $this->users_id      = $users_id ?? $this->users_id;        
        $this->loadPatients();
    }
}
