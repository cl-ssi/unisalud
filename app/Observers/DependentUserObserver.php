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
        $dependentUser->risks = [
            'flooded' => $dependentUser->user->address->location->flooded,
            'alluvium' => $dependentUser->user->address->location->alluvium
        ];
        $dependentUser->save();
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
