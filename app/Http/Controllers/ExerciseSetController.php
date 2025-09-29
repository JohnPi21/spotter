<?php

namespace App\Http\Controllers;

use App\Events\ExerciseSetSaved;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use App\Models\MesoDay;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Arr;

class ExerciseSetController extends Controller
{
    public function store(DayExercise $dayExercise)
    {
        $dayExercise->loadMissing(['day:id,mesocycle_id', 'day.mesocycle:id,user_id']);

        Gate::authorize('create', [ExerciseSet::class, $dayExercise->day]);

        $set = $dayExercise->sets()->create();

        ExerciseSetSaved::dispatch($set);

        return back();
    }

    public function update(Request $request, ExerciseSet $set): RedirectResponse
    {
        // @TODO: put this in a single DB transactions for rollback capabilities
        $set->load('dayExercise.day.mesocycle:id,user_id,days_per_week');

        $mesocycle = $set->dayExercise->day->mesocycle;

        $day = $set->dayExercise->day;

        Gate::authorize('update', $set);

        $validated = $request->validate([
            'reps'      => ['required', 'integer', 'max:64'],
            'weight'    => ['required', 'integer', 'max:1024'],
            'status'    => ['nullable', 'integer', 'in:0,1'],
        ]);

        $status = (int) Arr::pull($validated, 'status', 1);
        $validated['finished_at'] = $status === 1 ? now() : null;

        $set->update($validated);

        if ($set->dayExercise->sets->count() > 1) {
            $nextDay = MesoDay::where('mesocycle_id', $mesocycle->id)->where('week', $day->week + 1)->where('day_order', $day->day_order)->first();

            $nextDayExerciseID = $nextDay ? DayExercise::where('meso_day_id', $nextDay->id)
                ->where('exercise_id', $set->dayExercise->exercise_id)
                ->whereHas('day', fn(Builder $q) => $q->whereNull('finished_at'))
                ->ownedBy($mesocycle->user_id)->value('id')
                : null;

            if ($nextDayExerciseID) {
                ExerciseSet::create([
                    'day_exercise_id' => $nextDayExerciseID
                ]);
            }
        }

        return to_route('days.show', [$set->dayExercise->day->mesocycle, $set->dayExercise->day])->with('success', 'Set has been updated!');
    }

    public function destroy(ExerciseSet $set)
    {
        Gate::authorize('delete', $set);

        $set->delete();

        return redirect()->back();
    }
}
