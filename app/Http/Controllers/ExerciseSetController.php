<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSetRequest;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use App\Data\Set\UpdateData as SetUpdateData;

class ExerciseSetController extends Controller
{
    public function store(DayExercise $dayExercise)
    {
        $dayExercise->loadMissing(['day:id,mesocycle_id', 'day.mesocycle:id,user_id']);

        Gate::authorize('create', [ExerciseSet::class, $dayExercise->day]);

        $dayExercise->sets()->create();

        return back();
    }

    public function update(UpdateSetRequest $request, DayExercise $dayExercise, ExerciseSet $set): RedirectResponse
    {
        $updateSetDTO = SetUpdateData::from($request->validated());

        $set->update($updateSetDTO->toArray());

        return to_route('days.show', [$set->dayExercise->day->mesocycle, $set->dayExercise->day])->with('success', 'Set has been updated!');
    }

    public function destroy(DayExercise $dayExercise, ExerciseSet $set)
    {
        Gate::authorize('delete', $set);

        $set->delete();

        return redirect()->back();
    }
}
