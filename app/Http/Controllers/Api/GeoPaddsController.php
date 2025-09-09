<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DependentUser;
use Illuminate\Http\Request;

class GeoPaddsController extends Controller
{

    public function markers(Request $request)
    {
        // Obtener parámetros de filtro del query string
        $conditions_id = $request->input('conditions_id', []);
        $organizations_id = $request->input('organizations_id', []);
        $users_id = $request->input('users_id', []);
        $risks = $request->input('risks', []);

        // Convertir a arrays si vienen como strings JSON
        if (is_string($conditions_id)) {
            $conditions_id = json_decode($conditions_id, true) ?: [];
        }
        if (is_string($organizations_id)) {
            $organizations_id = json_decode($organizations_id, true) ?: [];
        }
        if (is_string($users_id)) {
            $users_id = json_decode($users_id, true) ?: [];
        }
        if (is_string($risks)) {
            $risks = json_decode($risks, true) ?: [];
        }

        // Query principal - exactamente como tu código original
        $dependentUsers = DependentUser::has('user.address.location')
            ->with(['user.address.location', 'conditions'])
            ->when(!empty($conditions_id), function ($q) use ($conditions_id) {
                foreach ($conditions_id as $condition_id) {
                    $q->whereHas('conditions', fn($qu) => $qu->where('condition_id', $condition_id));
                }
                return $q;
            })
            ->when(!empty($organizations_id), function ($q) use ($organizations_id) {
                $q->whereHas('user', function ($query) use ($organizations_id) {
                    $query->whereHas('mobileContactPoint', function ($query) use ($organizations_id) {
                        $query->whereHas('organization', function ($query) use ($organizations_id) {
                            $query->whereIn('id', $organizations_id);
                        });
                    });
                });
            })
            ->when(!empty($risks), function ($q) use ($risks) {
                foreach ($risks as $risk) {
                    $q->whereJsonContains('risks', [$risk]);
                }
            })
            ->when(!empty($users_id), fn($q) => $q->whereHas('user', fn($q) => $q->whereIn('id', $users_id)))
            ->whereHas('user', function ($query) {
                $query->whereHas('address', function ($query) {
                    $query->whereHas('location', function ($query) {
                        $query->whereNotNull('latitude');
                        $query->whereNotNull('longitude');
                    });
                });
            })
            ->get();

        // Transformar a markers - exactamente como tu código original
        $markers = $dependentUsers->map(function ($p) {
            $lat = $p->user->address->location->latitude;
            $lng = $p->user->address->location->longitude;

            if (!is_numeric($lat) || !is_numeric($lng)) {
                return null;
            }

            return [
                'id' => $p->id,
                'url' => route('filament.admin.resources.dependent-users.view', $p->id),
                'lat' => floatval($lat),
                'lng' => floatval($lng),
                'name' => $p->user->text ?? '',
                'address' => ($p->user->address->text ?? '') . ' ' . ($p->user->address->line ?? ''),
                'flooded' => in_array('Zona de Inundacion', $p->risks ?? []),
                'alluvium' => in_array('Zona de Aluvion', $p->risks ?? []),
            ];
        })->filter()->values()->toArray();

        return response()->json($markers);
    }
}
