<?php

namespace App\Http\Controllers;

use App\Actions\Mesocycle\ActivateMesocycle;
use App\Actions\Mesocycle\CreateMesocycle;
use App\Actions\Mesocycle\MesocycleToText;
use App\Actions\Mesocycle\ResolveActiveMesocycleDay;
use App\Actions\Mesocycle\UpdateMesocycle;
use App\Data\Mesocycle\CreateMesocycleData;
use App\Http\Requests\StoreMesocycleRequest;
use App\Models\Mesocycle;
use App\Services\MesocyclePlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MesocycleController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,mesocycle', only: ['edit', 'update', 'activate', 'destroy', 'exportAsText']),
        ];
    }

    public function index(): \Inertia\Response
    {
        $mesocycles = Mesocycle::query()->mine()->with(['days:id,finished_at,mesocycle_id'])->orderByDesc('status')->orderByDesc('updated_at')->get();

        $mesocycles->each(function (Mesocycle $meso) {
            $meso->setAttribute('last_edited', $meso->updated_at->diffForHumans());
        });

        return Inertia::render('mesocycles/index', [
            'title' => 'Mesocycles',
            'mesocycles' => $mesocycles,
        ]);
    }

    public function edit(Mesocycle $mesocycle, MesocyclePlan $plan): \Inertia\Response|RedirectResponse
    {
        try {
            $resolved = $plan->resolve($mesocycle);
        } catch (ValidationException) {
            return to_route('mesocycles')->with('error', 'This mesocycle has no usable saved plan or complete week to edit.');
        }

        $mesocycle->loadMissing('mesoTemplate');

        return Inertia::render('mesocycles/create', [
            ...$resolved['schema'],
            'canUpdateTemplate' => $mesocycle->mesoTemplate?->user_id === $mesocycle->user_id,
        ]);
    }

    public function update(StoreMesocycleRequest $request, Mesocycle $mesocycle, UpdateMesocycle $updateMesocycle): RedirectResponse
    {
        $mesoDTO = CreateMesocycleData::from($request->validated());

        $updateMesocycle->execute($mesoDTO, $mesocycle, $request->boolean('updateTemplate'));

        return to_route('mesocycles')->with('success', 'Mesocycle updated successfully.');
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('mesocycles/create');
    }

    public function store(StoreMesocycleRequest $request, CreateMesocycle $createMesocycle): \Illuminate\Http\RedirectResponse
    {
        $mesoDTO = CreateMesocycleData::from($request->validated());

        $userId = $request->user()->id;

        $userHasActiveMeso = (bool) Mesocycle::userHasActiveMeso($userId);

        $createMesocycle->execute($mesoDTO, $userId, $userHasActiveMeso);

        return to_route('mesocycles')->with('success', 'Mesocycle created succesfully.');
    }

    public function activate(Mesocycle $mesocycle, ActivateMesocycle $activateAction): RedirectResponse
    {
        $activateAction->execute($mesocycle);

        return to_route('mesocycles');
    }

    public function destroy(Mesocycle $mesocycle): RedirectResponse
    {
        $mesocycle->delete();

        return to_route('mesocycles');
    }

    public function currentActiveDay(ResolveActiveMesocycleDay $resolve): RedirectResponse
    {
        [$currentDayId, $mesocycleId] = $resolve->execute();

        return to_route('days.show', ['mesocycle' => $mesocycleId, 'day' => $currentDayId]);
    }

    public function exportAsText(Mesocycle $mesocycle, MesocycleToText $mesocycleToText): JsonResponse
    {
        $mesocycle->loadMissing(['days.dayExercises.exercise.muscleGroup', 'days.dayExercises.sets']);

        return response()->json([
            'text' => $mesocycleToText->execute($mesocycle),
            'message' => 'Mesocycle structure copied',
        ]);
    }
}
