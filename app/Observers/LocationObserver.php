<?php

namespace App\Observers;

use App\Models\Location;

class LocationObserver
{
    /**
     * Handle the Location "created" event.
     */
    public function created(Location $location): void
    {
        //
    }

    /**
     * Handle the Location "updated" event.
     */
    public function updated(Location $location): void
    {
        $dependentUser = $location->address->dependentUser;
        if ($dependentUser) {
            $risks = array_filter([
                $location?->flooded ? 'Zona de Inundacion' : null,
                $location?->alluvium ? 'Zona de Aluvion' : null,
            ]);
            if (isset($risks)) {
                $dependentUser->update(['risks' => $risks]);
            }
        }
    }

    /**
     * Handle the Location "deleted" event.
     */
    public function deleted(Location $location): void
    {
        //
    }

    /**
     * Handle the Location "restored" event.
     */
    public function restored(Location $location): void
    {
        //
    }

    /**
     * Handle the Location "force deleted" event.
     */
    public function forceDeleted(Location $location): void
    {
        //
    }
}
