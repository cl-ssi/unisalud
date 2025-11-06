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
    public $peo = 0;

    // public ?array $conditions_id = [];
    // public ?array $conditions_multiple = [];
    public ?string $conditions_type = null;
    public ?array $conditions_ids   = [];
    public ?array $organizations_id = [];
    public ?array $users_id         = [];
    public ?array $risks            = [];
    public array $patients          = [];
    public array $markers           = [];

    public function mount(?string $tipo = null, ?array $conditions = [], ?array $organizations_id = [], ?array $risks = [], ?array $users_id = []): void
    {
        $this->baseUrl          = env('APP_URL', 'https://uni.saludtarapaca.gob.cl/');
        $this->conditions_type    = $tipo;
        $this->conditions_ids    = array_values($conditions);
        $this->organizations_id = $organizations_id;
        $this->users_id = $users_id;
        $this->risks = $risks;
        $this->loadPatients();
    }

    private function loadPatients(): void
    {

        $dependentUsers = DependentUser::has('user.address.location')
            ->with(['user.address.location', 'conditions'])
            ->when($this->conditions_ids && $this->conditions_type, function ($query) {
                if ($this->conditions_type == 'u' && $this->conditions_ids) {
                    return $query->whereHas('conditions', fn($q) => $q->whereIn('condition_id', $this->conditions_ids));
                } else if ($this->conditions_type == 'v' && $this->conditions_ids) {
                    foreach ($this->conditions_ids as $condition_id) {
                        $query->whereHas('conditions', fn($q) => $q->where('condition_id', $condition_id));
                    }
                    return $query;
                } else {
                    return $query;
                }
            })
            ->when($this->organizations_id, function ($q) {
                $q->whereHas('user', function ($query) {
                    $query->whereHas('mobileContactPoint', function ($query) {
                        $query->whereHas('organization', function ($query) {
                            $query->whereIn('id', $this->organizations_id);
                        });
                    });
                });
            })
            ->when($this->risks, function ($q) {
                foreach ($this->risks as $risk) {
                    $q->whereJsonContains('risks', [$risk]);
                }
            })
            ->when($this->users_id, fn($q) => $q->whereHas('user', fn($q) => $q->whereIn('id', $this->users_id))) // Updated to use $this->users_id

            ->whereHas('user', function ($query) {
                $query->whereHas('address', function ($query) {
                    $query->whereHas('location', function ($query) {
                        $query->whereNotNull('latitude')->where('latitude', '<>', '');
                        $query->whereNotNull('longitude')->where('longitude', '<>', '');
                    });
                });
            })
            ->get();

        $this->markers = $dependentUsers->map(function ($p) {
            $lat = $p->user->address->location->latitude;
            $lng = $p->user->address->location->longitude;
            if (!is_numeric($lat) || !is_numeric($lng)) {
                return null;
            }
            return [
                'id'   => $p->id,
                'url'   => route('filament.admin.resources.dependent-users.view', $p->id),
                'lat'   => floatval($lat),
                'lng'   => floatval($lng),
                'name'    => $p->user->text,
                'address' => $p->user->address->text . ' ' . $p->user->address->line,
                'flooded' => in_array('Zona de Inundacion', $p->risks),
                'alluvium' => in_array('Zona de Aluvion', $p->risks),
            ];
        })->filter()->values()->toArray();
    }

    #[On('changeFilters')]
    public function changeFilters(?string $tipo = null, ?array $conditions = [], ?array $organizations_id = [], ?array $users_id = null, ?array $risks = []): void
    {
        $this->conditions_type    = $tipo ?? $this->conditions_type;
        $this->conditions_ids    = array_values($conditions) ?? $this->conditions_ids;
        $this->organizations_id = $organizations_id ?? $this->organizations_id;
        $this->users_id      = $users_id ?? $this->users_id;
        $this->risks      = $risks ?? $this->risks;
        $this->loadPatients();

        $this->dispatch('markersUpdated', ['markers' => $this->markers]);
    }
}
