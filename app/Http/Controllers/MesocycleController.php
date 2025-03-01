<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\MuscleGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class MesocycleController extends Controller
{
    public function index(): \Inertia\Response
    {
        $mesocycles = Mesocycle::all();

        return Inertia::render('mesocycles/index', [
            'title'     => 'Mesocycles',
            'mesocycles' => $mesocycles
        ]);
    }

    public function show(Mesocycle $mesocycle): \Inertia\Response
    {
        $mesocycle = Mesocycle::with([
            'days.exercises' => ['muscleGroup', 'sets']
        ])->find($mesocycle->id);

        return Inertia::render('mesocycles/show', [
            'title'     => 'Mesocycle',
            'mesocycle' => $mesocycle
        ]);
    }


    public function create(): \Inertia\Response
    {
        $muscleGroups = MuscleGroup::pluck('name', 'id')->toArray();
        $exercises = Exercise::all();
        $exerciseDropdown = [];

        foreach ($exercises as $option) {
            $exerciseDropdown[$option->muscle_group_id][] = ['value' => $option->id, 'label' => $option->name];
        }

        return Inertia::render('mesocycles/create', [
            'muscleGroups' => $muscleGroups,
            'exercises' => $exercises,
            'exerciseDropdown' => $exerciseDropdown,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {

        $muscleGroups = MuscleGroup::pluck('id')->toArray();

        $validated = $request->validate([
            'meso.name'                         => ['string'],
            'meso.unit'                         => [Rule::in(['kg', 'lbs'])],
            'meso.weeksDuration'                => ['integer', 'min:3', 'max:6'],
            'days'                              => ['array'],
            'days.*.label'                      => ['string'],
            'days.*.exercises'                  => ['array'],
            'days.*.exercises.*.muscleGroup'    => ['integer', 'min:1', Rule::in($muscleGroups)],
            'days.*.exercises.*.exerciseId'     => ['integer', 'min:1', 'exists:exercises,id']
        ]);

        $mesocycle = new Mesocycle();

        $validatedMeso = collect($validated['meso']);

        $mesocycle->name            = $validatedMeso['name'];
        $mesocycle->unit            = $validatedMeso['unit'];
        $mesocycle->weeks_duration  = $validatedMeso['weeksDuration'];
        $mesocycle->days_per_week   = count($validated['days']);
        $mesocycle->user_id         = 1;
        $mesocycle->status          = 0;

        $mesocycle->save();

        for ($i = 1; $i <= $mesocycle['weeks_duration']; $i++) {

            foreach ($validated['days'] as $idx => $day) {
                $createdDay = MesoDay::create([
                    "mesocycle_id" => $mesocycle['id'],
                    "week"         => $i,
                    "day_order"    => $idx + 1,
                    "label"        => $day['label'],
                    "position"     => $idx,
                    "status"       => 0,
                ]);

                foreach ($day['exercises'] as $position => $exercise) {
                    $exerciseDay = DayExercise::create([
                        "meso_day_id" => $createdDay->id,
                        "exercise_id" => $exercise['exerciseId'],
                        "position"    => (int)$position,
                    ]);

                    ExerciseSet::create([
                        "day_exercise_id"   => $exerciseDay->id,
                        "status"            => 0
                    ]);
                }
            }
        }


        return to_route('mesocycles');
    }

    public function activate(Mesocycle $mesocycle): RedirectResponse
    {
        Mesocycle::where('user_id', 1)->update(['status' => 0]);

        $mesocycle->status = 1;
        $mesocycle->save();

        return to_route('mesocycles');
    }

    public function destroy(Mesocycle $mesocycle): RedirectResponse
    {
        $mesocycle->delete();

        return to_route('mesocycles');
    }

    public function getDay(Mesocycle $mesocycle, MesoDay $day): \Inertia\Response
    {
        $mesocycle->load('days:id,mesocycle_id,label');

        $day->load(['dayExercises' => ['exercise' => ['muscleGroup'], 'sets']]);
        // dd($day);

        $calendar = [];
        $weekIdx = 1;

        foreach ($mesocycle->days as $idx => $d) {
            $calendar[$weekIdx][] = $d;

            if (count($calendar[$weekIdx]) == $mesocycle->days_per_week) {
                $weekIdx++;
            }
        }

        $mesocycle->calendar = $calendar;
        // dd($calendar);

        // Split days in days / week

        // $week_days = MesoDay::where('mesocycle_id', $mesocycle->id)
        //     ->where('week', $day->week)
        //     ->get();

        // $day->order = $week_days->search(function ($d) use ($day) {
        //     return $d->id == $day->id;
        // }) + 1;

        $mesocycle->setRelation('day', $day);

        return Inertia::render('mesocycles/show', ['mesocycle' => $mesocycle]);
    }
}
