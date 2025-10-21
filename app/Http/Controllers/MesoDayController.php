<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Models\DayExercise;
use Inertia\Inertia;
use App\Models\MesoDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use App\Actions\Mesocycle\MakeMesocycleCalendar;
use App\Events\DayFinished;
use Illuminate\Support\Facades\DB;

class MesoDayController extends Controller
{
    public function show(int $mesocycle, MesoDay $day, MakeMesocycleCalendar $makeCalendarAction): \Inertia\Response
    {

        $day->load(['dayExercises' => fn($q) => $q->orderBy('position'), 'dayExercises.exercise.muscleGroup', 'dayExercises.sets', 'mesocycle']);

        Gate::authorize('view', $day);

        $mesocycle = $day->mesocycle;

        $calendar = $makeCalendarAction->execute($mesocycle);

        $mesocycle->calendar = $calendar;
        $mesocycle->makeHidden('days');
        $day->makeHidden('mesocycle');

        $mesocycle->setRelation('day', $day);

        return Inertia::render('mesocycles/show', ['mesocycle' => $mesocycle]);
    }


    public function toggleDay(MesoDay $day): RedirectResponse
    {
        Gate::authorize('owns', $day);

        DB::transaction(function () use ($day) {
            DB::table('meso_days')->lockForUpdate()->find($day->getKey());

            if (! $day->canFinish()) {
                throw new AppException(422, __('All sets must be completed'), 'SETS_UNFINISHED');
            }

            $day->update(['finished_at' => $day->finished_at ? null : now()]);

            DayFinished::dispatchIf($day->finished_at, $day->id);
        });

        return redirect()->back()->with('day', $day->finished_at);
    }
}
