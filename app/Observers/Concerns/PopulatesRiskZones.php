<?php

declare(strict_types=1);

namespace App\Observers\Concerns;

use Illuminate\Database\Eloquent\Model;

trait PopulatesRiskZones
{
    /**
     * Populate risks from the user's geocoded address location.
     */
    protected function populateRiskZones(Model $record): void
    {
        $risks = [];
        if ($record->user?->address?->location) {
            $location = $record->user->address->location;
            if ($location->flooded) {
                $risks[] = 'Zona de Inundacion';
            }
            if ($location->alluvium) {
                $risks[] = 'Zona de Aluvion';
            }
        }

        if (isset($risks)) {
            $record->update(['risks' => $risks]);
        }
    }
}
