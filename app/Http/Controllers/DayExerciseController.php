<?php

namespace App\Http\Controllers;

use App\Actions\DayExercise\CreateAction as DayExerciseCreateAction;
use App\Http\Requests\StoreDayExerciseRequest;
use App\Http\Requests\UpdateDayExerciseOrderRequest;
use App\Models\DayExercise;
use App\Models\MesoDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DayExerciseController extends Controller
{
    public function store(StoreDayExerciseRequest $request, MesoDay $day): RedirectResponse
    {
        DayExerciseCreateAction::execute($request->validated('exercise_id'), $day);

        return redirect()->route('days.show', [
            'mesocycle' => $day->mesocycle,
            'day'       => $day,
        ]);
    }

    // @TODO: Move this into an action
    public function updateOrder(UpdateDayExerciseOrderRequest $request, MesoDay $day): RedirectResponse
    {
        $order = collect($request->validated('order'));

        DB::transaction(function () use ($order, $day) {
            $dayExercises = $day->dayExercises()->lockForUpdate()->pluck('id');

            if ($dayExercises->diff($order->all())->isNotEmpty()) {
                throw ValidationException::withMessages(['order' => 'Not all exercises belong to the current day.']);
            }

            if ($order->diff($dayExercises->all())->isNotEmpty()) {
                throw ValidationException::withMessages(['order' => 'Not all exercises belong to the current day.']);
            }

            $placeholders = implode(',', array_fill(0, $order->count(), '?'));

            DB::update("
                UPDATE day_exercises
                SET position = position + 100
                where meso_day_id = ?
            ", [$day->id]);

            DB::update("
                UPDATE day_exercises
                SET position = FIELD(id, $placeholders)
                WHERE meso_day_id = ?
            ", [...$order->all(), $day->id]);
        });

        return to_route('days.show', [
            'mesocycle' => $day->mesocycle,
            'day'       => $day,
        ]);
    }

    // @TODO: check the day from dayexercise is the same as $day sent in request
    public function destroy(MesoDay $day, DayExercise $exercise): RedirectResponse
    {
        Gate::authorize('owns', $day->mesocycle);

        $exercise->load('day.mesocycle');

        $exercise->day->ensureIsEditable();

        $exercise->delete();

        return redirect()->route('days.show', [
            'mesocycle' => $exercise->day->mesocycle,
            'day'       => $exercise->day,
        ]);
    }
}
