<?php

namespace App\Policies;

use App\Models\SigteSurgicalWaitlist;
use App\Models\User;

class SigteSurgicalWaitlistPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('SIGTE LE QX: listado');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SigteSurgicalWaitlist $sigteSurgicalWaitlist): bool
    {
        return $user->can('SIGTE LE QX: listado');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('SIGTE LE QX: ingresar paciente');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SigteSurgicalWaitlist $sigteSurgicalWaitlist): bool
    {
        return $user->can('SIGTE LE QX: listado') && is_null($sigteSurgicalWaitlist->exported_at);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SigteSurgicalWaitlist $sigteSurgicalWaitlist): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SigteSurgicalWaitlist $sigteSurgicalWaitlist): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SigteSurgicalWaitlist $sigteSurgicalWaitlist): bool
    {
        return false;
    }
}
