<?php

namespace App\Policies;

use App\Models\MesoDay;
use App\Models\User;
use Illuminate\Http\Response;

class MesoDayPolicy
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
    public function view(User $user, MesoDay $mesoDay): bool
    {
        return $user->id === $mesoDay->mesocycle->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }


    public function owns(User $user, MesoDay $mesoDay): bool
    {
        return $mesoDay->mesocycle->user_id == $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MesoDay $mesoDay): Response | bool
    {
        if (! $mesoDay->isEditable()) {
            return Response::deny('This day is finished and cannot be edited.');
        }

        return $this->owns($user, $mesoDay);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MesoDay $mesoDay): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MesoDay $mesoDay): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MesoDay $mesoDay): bool
    {
        return false;
    }
}
