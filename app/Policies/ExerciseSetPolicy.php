<?php

namespace App\Policies;

use App\Models\ExerciseSet;
use App\Models\MesoDay;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExerciseSetPolicy
{
    protected function denyFinished(): Response
    {
        return Response::deny('This day is finished and cannot be edited.');
    }

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
    public function view(User $user, ExerciseSet $set): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, MesoDay $day): Response | bool
    {
        $day->loadMissing('mesocycle');

        if (! $day->isEditable()) {
            return $this->denyFinished();
        }

        return $day->mesocycle->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExerciseSet $set): Response | bool
    {
        $set->loadMissing('dayExercise.day.mesocycle');

        $day = $set->dayExercise->day;

        if (! $day->isEditable()) {
            return $this->denyFinished();
            // return false;
        }

        return $day->mesocycle->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExerciseSet $set): Response | bool
    {
        $set->loadMissing('dayExercise.day.mesocycle');

        $day = $set->dayExercise->day;

        if (! $day->isEditable()) {
            return $this->denyFinished();
        }

        return $day->mesocycle->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExerciseSet $set): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExerciseSet $set): bool
    {
        return false;
    }
}
