<?php

namespace App\Observers;

use App\Models\DependentUser;

class DependentUserObserver
{
    /**
     * Handle the DependentUser "created" event.
     */
    public function created(DependentUser $dependentUser): void
    {
        $location = $dependentUser->user->address->location ?? null;
        $risks = array_filter([
            $location?->flooded ? 'Zona de Inundacion' : null,
            $location?->alluvium ? 'Zona de Aluvion' : null,
        ]);
        if (isset($risks)) {
            $dependentUser->update(['risks' => $risks]);
        }
    }

    /**
     * Handle the DependentUser "updated" event.
     */
    public function updated(DependentUser $dependentUser): void
    {
        //
    }

    /**
     * Handle the DependentUser "deleted" event.
     */
    public function deleted(DependentUser $dependentUser): void
    {
        //
    }

    /**
     * Handle the DependentUser "restored" event.
     */
    public function restored(DependentUser $dependentUser): void
    {
        //
    }

    /**
     * Handle the DependentUser "force deleted" event.
     */
    public function forceDeleted(DependentUser $dependentUser): void
    {
        //
    }
}
