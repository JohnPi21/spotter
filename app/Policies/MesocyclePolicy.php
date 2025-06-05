<?php

namespace App\Policies;

use App\Models\Mesocycle;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MesocyclePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Mesocycle $mesocycle): bool
    {
        // @TODO: Also add friends of user (with a pivot table)
        return $user->id === $mesocycle->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Mesocycle $mesocycle): bool
    {
        return $user->id === $mesocycle->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Mesocycle $mesocycle): bool
    {
        return $user->id === $mesocycle->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Mesocycle $mesocycle): bool
    {
        return $user->id === $mesocycle->user_id;
    }

    public function owns(User $user, Mesocycle $mesocycle): bool
    {
        return $user->id === $mesocycle->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, Mesocycle $mesocycle): bool
    // {
        //
    // }
}
