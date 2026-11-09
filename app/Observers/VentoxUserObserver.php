<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\VentoxUser;
use App\Observers\Concerns\PopulatesRiskZones;

class VentoxUserObserver
{
    use PopulatesRiskZones;

    /**
     * Handle the VentoxUser "created" event.
     */
    public function created(VentoxUser $ventoxUser): void
    {
        $this->populateRiskZones($ventoxUser);
    }

    /**
     * Handle the VentoxUser "updated" event.
     */
    public function updated(VentoxUser $ventoxUser): void
    {
        //
    }

    /**
     * Handle the VentoxUser "deleted" event.
     */
    public function deleted(VentoxUser $ventoxUser): void
    {
        //
    }

    /**
     * Handle the VentoxUser "restored" event.
     */
    public function restored(VentoxUser $ventoxUser): void
    {
        //
    }

    /**
     * Handle the VentoxUser "force deleted" event.
     */
    public function forceDeleted(VentoxUser $ventoxUser): void
    {
        //
    }
}
