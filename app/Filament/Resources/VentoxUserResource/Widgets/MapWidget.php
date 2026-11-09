<?php

declare(strict_types=1);

namespace App\Filament\Resources\VentoxUserResource\Widgets;

use App\Filament\Resources\VentoxUserResource;
use App\Filament\Widgets\BaseMapWidget;
use App\Models\VentoxUser;

class MapWidget extends BaseMapWidget
{
    protected static string $view = 'filament.resources.ventox-user-resource.widgets.map-widget';

    protected function loadPatients(): void
    {
        $ventoxUsers = VentoxUser::has('user.address.location')
            ->with(['user.address.location'])
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
            ->when($this->users_id, fn($q) => $q->whereHas('user', fn($q) => $q->whereIn('id', $this->users_id)))
            ->whereHas('user', function ($query) {
                $query->whereHas('address', function ($query) {
                    $query->whereHas('location', function ($query) {
                        $query->whereNotNull('latitude')->where('latitude', '<>', '');
                        $query->whereNotNull('longitude')->where('longitude', '<>', '');
                    });
                });
            })
            ->get();

        $this->markers = $ventoxUsers->map(function ($p) {
            $lat = $p->user->address?->location?->latitude;
            $lng = $p->user->address?->location?->longitude;
            if (!is_numeric($lat) || !is_numeric($lng)) {
                return null;
            }

            return [
                'id'       => $p->id,
                'url'      => VentoxUserResource::getUrl('view', [$p->id]),
                'lat'      => floatval($lat),
                'lng'      => floatval($lng),
                'name'     => $p->user->text,
                'address'  => $p->user->address->text . ' ' . $p->user->address->line,
                'flooded'  => in_array('Zona de Inundacion', $p->risks ?? []),
                'alluvium' => in_array('Zona de Aluvion', $p->risks ?? []),
            ];
        })->filter()->values()->toArray();
    }
}
