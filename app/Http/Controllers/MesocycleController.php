<?php

namespace App\Http\Controllers;

use App\Actions\Mesocycle\CreateAction;
use App\Data\Mesocycle\CreateData as MesocycleCreateData;
use App\Http\Requests\StoreMesocycleRequest;
use App\Models\Mesocycle;
use App\Actions\Mesocycle\ActivateAction as MesocycleAcivateAction;
use App\Actions\Mesocycle\ResolveActiveMesocycleDayAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\Middleware;

class MesocycleController extends Controller implements HasMiddleware
{

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,mesocycle', only: ['update', 'activate', 'destroy']),
        ];
    }


    public function index(): \Inertia\Response
    {
        $mesocycles = Mesocycle::mine()->with(['days:id,finished_at,mesocycle_id'])->get();

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

    public function store(StoreMesocycleRequest $request): \Illuminate\Http\RedirectResponse
    {
        $mesoDTO = MesocycleCreateData::from($request->validated());

        CreateAction::execute($mesoDTO);

        return to_route('mesocycles')->with('success', 'Mesocycle created succesfully.');
    }

    public function activate(Mesocycle $mesocycle): RedirectResponse
    {
        MesocycleAcivateAction::execute($mesocycle);

        return to_route('mesocycles');
    }

    public function destroy(Mesocycle $mesocycle): RedirectResponse
    {
        $mesocycle->delete();

        return to_route('mesocycles');
    }


    public function currentActiveDay(ResolveActiveMesocycleDayAction $resolve): RedirectResponse
    {
        [$currentDayId, $mesocycleId] = $resolve();

        return to_route("days.show", ['mesocycle' => $mesocycleId, 'day' => $currentDayId]);
    }
}
