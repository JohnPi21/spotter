<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\MuscleGroup;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MesocycleController extends Controller implements HasMiddleware
{

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,mesocycle', only: ['update', 'activate', 'destroy']),
            new Middleware('can:view,mesocycle', only: ['show']),
            // new Middleware('can:create,' . Mesocycle::class, only: ['create']),
        ];
    }


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
        return Inertia::render('mesocycles/create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $muscleGroups = MuscleGroup::pluck('id')->toArray();

        $validated = $request->validate([
            'meso'                              => ['required', 'array'],
            'meso.name'                         => ['required', 'string'],
            'meso.unit'                         => ['sometimes', Rule::in(['kg', 'lbs'])],
            'meso.weeksDuration'                => ['required', 'integer', 'min:3', 'max:12'],
            'days'                              => ['required', 'array'],
            'days.*.label'                      => ['required', 'string'],
            'days.*.exercises'                  => ['required', 'array'],
            'days.*.exercises.*.muscleGroup'    => ['required', 'integer', 'min:1', Rule::in($muscleGroups)],
            'days.*.exercises.*.exerciseID'     => ['required', 'integer', 'min:1', 'exists:exercises,id']
        ]);

        $validatedMeso = collect($validated['meso']);

        $activateMeso = ! Mesocycle::mine()->exists();

        $mesocycle = Mesocycle::create([
            'name'            => $validatedMeso->get('name'),
            'unit'            => $validatedMeso->get('unit', 'kg'),
            'weeks_duration'  => $validatedMeso->get('weeksDuration'),
            'days_per_week'   => count($validated['days']),
            'user_id'         => $request->user()->id,
            'status'          => $activateMeso
        ]);

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
                        "exercise_id" => $exercise['exerciseID'],
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
        $oldMeso = Mesocycle::mine()->active()->first();

        if (! is_null($oldMeso)) {
            $oldMeso->update(['status' => Mesocycle::STATUS_INACTIVE]);
        }

        $mesocycle->update(['status' => Mesocycle::STATUS_ACTIVE]);

        return to_route('mesocycles');
    }

    public function destroy(Mesocycle $mesocycle): RedirectResponse
    {
        $mesocycle->delete();

        return to_route('mesocycles');
    }


    public function currentActiveDay(): RedirectResponse
    {
        $mesocycle = Mesocycle::mine()->where('status', 1)->first();

        if (is_null($mesocycle)) {
            throw new \App\Exceptions\AppException("No active mesocycle", 404);
            // return redirect()->route('mesocycles')->withErrors([
            //     'mesocycle' => 'No active mesocycle found.',
            // ]);
        }

        // $days = [16, 15, 14, 13, 12];
        // $day[13] has status 1 meaning i have to get $day[14] next;
        $days = MesoDay::where('mesocycle_id', $mesocycle->id)->orderBy('id', 'DESC')->get();

        $index = $days->search(fn($day) => $day->status === 1);

        $currentDay = [];

        if ($index && isset($days[$index - 1])) {
            $currentDay = $days[$index - 1];
        } elseif ($index !== false) {
            $currentDay = $days[$index];
        } else {
            $currentDay = $days->last();
        }

        return to_route("day.show", ['mesocycle' => $mesocycle->id, 'day' => $currentDay->id]);
    }
}
