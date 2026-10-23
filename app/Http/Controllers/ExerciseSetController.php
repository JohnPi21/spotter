<?php

namespace App\Http\Controllers;

use App\Data\Set\UpdateSetData;
use App\Http\Requests\UpdateSetRequest;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use Illuminate\Http\JsonResponse;
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

    public function update(UpdateSetRequest $request, DayExercise $dayExercise, ExerciseSet $set): JsonResponse|RedirectResponse
    {
        $updateSetDTO = UpdateSetData::from($request->validated());

        $set->update($updateSetDTO->toArray());

        return response()->json(['set' => $set->refresh()->withoutRelations()]);
    }

    public function destroy(DayExercise $dayExercise, ExerciseSet $set)
    {
        Gate::authorize('delete', $set);

        $set->delete();

        return redirect()->back();
    }
}
