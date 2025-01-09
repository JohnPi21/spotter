<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\User;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MesocycleController extends Controller
{
    public function index(): \Inertia\Response
    {
        $mesocycles = Mesocycle::all();

        return Inertia::render('mesocycles/index', [
            'mesocycles' => $mesocycles
        ]);
    }

    public function show(Mesocycle $mesocycle): \Inertia\Response
    {
        $mesocycle = Mesocycle::with([
            'days.exercises' => ['muscleGroup', 'sets']
        ])->find($mesocycle->id);

        return Inertia::render('mesocycles/show', ['mesocycle' => $mesocycle]);
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
            'meso.weeks'                        => ['integer', 'min:3', 'max:6'],
            'days'                              => ['array'],
            'days.*.label'                      => ['string'],
            'days.*.exercises'                  => ['array'],
            'days.*.exercises.*.muscleGroup'    => ['integer', 'min:1', Rule::in($muscleGroups)],
            'days.*.exercises.*.exerciseId'     => ['integer', 'min:1', 'exists:exercises,id']
        ]);

        $mesocycle = new Mesocycle();

        $validatedMeso = collect($validated['meso']);

        $mesocycle->name    = $validatedMeso['name'];
        $mesocycle->unit    = $validatedMeso['unit'];
        $mesocycle->weeks   = $validatedMeso['weeks'];
        $mesocycle->days    = count($validated['days']);
        $mesocycle->user_id = 1;
        $mesocycle->status  = 1;

        $mesocycle->save();

        for ($i = 1; $i <= $mesocycle['weeks']; $i++) {

            foreach ($validated['days'] as $idx => $day) {
                $createdDay = MesoDay::create([
                    "mesocycle_id" => $mesocycle['id'],
                    "week"         => $i,
                    "label"        => $day['label'],
                    "position"     => $idx,
                    "status"       => 0,
                ]);

                foreach ($day['exercises'] as $position => $exercise) {
                    DayExercise::create([
                        "meso_day_id" => $createdDay->id,
                        "exercise_id" => $exercise['exerciseId'],
                        "position"    => (int)$position,
                    ]);
                }
            }
        }


        return to_route('mesocycles');
    }

    public function getDay(Mesocycle $mesocycle, $dayID): \Inertia\Response
    {
        $day = MesoDay::with(['exercises' => ['sets', 'muscleGroup']])->findOrFail($dayID);

        $mesocycle->setRelation('day', $day);

        $mesocycle->unsetRelation('days');

        return Inertia::render('mesocycles/show', ['mesocycle' => $mesocycle]);
    }
}
