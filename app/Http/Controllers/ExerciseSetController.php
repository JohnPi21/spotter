<?php

namespace App\Http\Controllers;

use App\Actions\Set\UpdateAction;
use App\Http\Requests\UpdateSetRequest;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ExerciseSetController extends Controller
{
    public function store(DayExercise $dayExercise)
    {
        $dayExercise->loadMissing(['day:id,mesocycle_id', 'day.mesocycle:id,user_id']);

        Gate::authorize('create', [ExerciseSet::class, $dayExercise->day]);

        $dayExercise->sets()->create();

        return back();
    }

    public function update(UpdateSetRequest $request, int $dayExercise, ExerciseSet $set): RedirectResponse
    {
        UpdateAction::execute($request, $set);

        return to_route('days.show', [$set->dayExercise->day->mesocycle, $set->dayExercise->day])->with('success', 'Set has been updated!');
    }

    public function destroy(ExerciseSet $set)
    {
        Gate::authorize('delete', $set);

        $set->delete();

        return redirect()->back();
    }
}
