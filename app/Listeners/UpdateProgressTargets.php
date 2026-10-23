<?php

namespace App\Listeners;

use App\Events\DayToggled;
use App\Models\ExerciseSet;
use App\Models\MesoDay;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class UpdateProgressTargets implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public $delay = 5;

    /**
     * Handle the event.
     */
    public function handle(DayToggled $event): void
    {
        $day = MesoDay::with(['mesocycle:id,days_per_week,weeks_duration', 'dayExercises.sets'])->find($event->dayId);

        if (! $day || ! $day->isFinished()) {
            return;
        }

        DB::transaction(function () use ($day) {
            /** @var MesoDay|null */
            $nextDaySibling = $day->nextWeekSibling()->select('id')->first();

            if (! $nextDaySibling) {
                return;
            }

            $nextDayExercises = $nextDaySibling->dayExercises()
                ->lockForUpdate()
                ->get()
                ->keyBy('exercise_id');

            $nextDayTargets = $day->dayExercises->flatMap(function ($dayExercise) use ($nextDayExercises) {
                $nextDayExerciseId = $nextDayExercises->get($dayExercise->exercise_id)?->id;

                if (! $nextDayExerciseId) {
                    return null;
                }

                logger('The day', ['test']);
                logger('Finished day', ['dayExercises' => $dayExercise->toArray()]);

                return $dayExercise->sets->map(fn ($set) => [
                    'day_exercise_id' => $nextDayExercises->get($dayExercise->exercise_id)->id,
                    'min_reps' => $set->min_reps,
                    'max_reps' => $set->max_reps,
                    'min_rir' => $set->min_rir,
                    'max_rir' => $set->max_rir,
                    'target_weight' => $set->weight,
                    'target_reps' => $set->reps,
                    'finished_at' => null,
                ])->toArray();
            })->toArray();

            if (empty($nextDayTargets)) {
                return;
            }

            ExerciseSet::whereIn('day_exercise_id', $nextDayExercises->pluck('id'))->delete();

            ExerciseSet::insert($nextDayTargets);
        });
    }

    public function shouldQueue(DayToggled $event): bool
    {
        $day = MesoDay::with(['mesocycle:id,weeks_duration'])->select('id', 'week', 'mesocycle_id', 'finished_at')->find($event->dayId);

        return $day && $day->isFinished() && $day->mesocycle->weeks_duration > $day->week;
    }
}
