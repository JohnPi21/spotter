<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Models\DayExercise;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\ExerciseSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\Middleware;
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
            // new Middleware('can:view,mesocycle', only: ['show']),
            // new Middleware('can:create,' . Mesocycle::class, only: ['create']),
        ];
    }


    public function index(): \Inertia\Response
    {
        $mesocycles = Mesocycle::mine()->with(['days:id,finished_at,mesocycle_id'])->get();

        // On each mesocycle add the id (needed for building hte rul) 
        // to the Current active day OR last day if the mesocycle is finished
        $mesocycles->each(
            fn($mesocycle) =>
            $mesocycle->lastDay = $mesocycle->days
                ->firstWhere('finished_at', null)?->id ?? $mesocycle->days->last()?->id
        );

        return Inertia::render('mesocycles/index', [
            'title'     => 'Mesocycles',
            'mesocycles' => $mesocycles
        ]);
    }

    // CANCEL THIS ROUTE
    public function show(Mesocycle $mesocycle): \Inertia\Response
    {
        $mesocycle->load([
            'days.dayExercises' => ['exercise.muscleGroup', 'sets']
        ]);

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
        // $muscleGroups = MuscleGroup::pluck('id')->toArray();

        $validated = $request->validate([
            'meso'                              => ['required', 'array'],
            'meso.name'                         => ['required', 'string'],
            'meso.unit'                         => ['sometimes', Rule::in(['kg', 'lbs'])],
            'meso.weeksDuration'                => ['required', 'integer', 'min:3', 'max:12'],
            'days'                              => ['required', 'array', 'min:1', 'max:7'],
            'days.*.label'                      => ['required', 'string', 'min:1', 'max:64'],
            'days.*.exercises'                  => ['required', 'array', 'min:1', 'max:32'],
            'days.*.exercises.*.muscleGroup'    => ['required', 'integer', 'min:1', 'exists:muscle_groups,id'],
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
                ]);

                foreach ($day['exercises'] as $position => $exercise) {
                    $exerciseDay = DayExercise::create([
                        "meso_day_id" => $createdDay->id,
                        "exercise_id" => $exercise['exerciseID'],
                        "position"    => (int)$position,
                    ]);

                    ExerciseSet::create([
                        "day_exercise_id"   => $exerciseDay->id,
                    ]);
                }
            }
        }

        return to_route('mesocycles')->with('success', 'Mesocycle created succesfully.');
    }

    public function activate(Mesocycle $mesocycle): RedirectResponse
    {
        DB::transaction(function () use ($mesocycle) {

            $mesocycle->lockForUpdate();

            $hasUnfinishedDays = $mesocycle->days()->whereNull('finished_at')->exists();

            if (! $hasUnfinishedDays) {
                throw new AppException(422, __('Mesocycle cannot activate (no unfinished days)'), 'NO_UNFINISHED_DAYS');
            }

            Mesocycle::mine()->active()->whereKeyNot($mesocycle->getKey())->update(['status' => Mesocycle::STATUS_INACTIVE]);

            $mesocycle->update(['status' => Mesocycle::STATUS_ACTIVE]);
        });

        return to_route('mesocycles');
    }

    public function destroy(Mesocycle $mesocycle): RedirectResponse
    {
        $mesocycle->delete();

        return to_route('mesocycles');
    }


    public function currentActiveDay(): RedirectResponse
    {
        $mesocycle = Mesocycle::mine()->where('status', Mesocycle::STATUS_ACTIVE)->first();

        if (! $mesocycle) {
            throw new AppException(404, __("No active mesocycle"), "NO_ACTIVE_MESOCYCLE");
        }

        // $days = [16, 15, 14, 13, 12];
        // $day[13] has status 1 meaning I have to get $day[14] next;

        $currentDay = MesoDay::where('mesocycle_id', $mesocycle->id)->orderBy('id')->whereNull('finished_at')->value('id')
            ?? MesoDay::where('mesocycle_id', $mesocycle->id)->orderByDesc('id')->value('id');


        if (! $currentDay) {
            throw new AppException(404, __("No day found for mesocycle"), 'NO_DAY_FOUND');
        }

        return to_route("days.show", ['mesocycle' => $mesocycle->id, 'day' => $currentDay->id]);
    }
}
