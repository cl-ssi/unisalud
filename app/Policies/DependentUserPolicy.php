<?php

namespace App\Policies;

use App\Models\DependentUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DependentUserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DependentUser $dependentUser): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('geopadds_user') || $user->hasRole('geopadds_admin') || $user->can('be god');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DependentUser $dependentUser): bool
    {
        return $user->hasRole('geopadds_user') || $user->hasRole('geopadds_admin') || $user->can('be god');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DependentUser $dependentUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DependentUser $dependentUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DependentUser $dependentUser): bool
    {
        return false;
    }
}
