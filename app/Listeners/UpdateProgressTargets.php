<?php

namespace App\Listeners;

use App\Events\DayFinished;
use App\Models\ExerciseSet;
use App\Models\MesoDay;
use DB;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProgressTargets implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct() {}

    public $delay = 5;

    /**
     * Handle the event.
     */
    public function handle(DayFinished $event): void
    {
        $day = MesoDay::with(['mesocycle:id,days_per_week,weeks_duration', 'dayExercises.sets'])->find($event->dayId);

        DB::transaction(function () use ($day) {
            $nextDaySibling = $day->nextWeekSibling()->select('id')->first();

            $nextDayExercises = $nextDaySibling->dayExercises()
                ->lockForUpdate()
                ->get()
                ->keyBy('exercise_id');

            $nextDayTargets = $day->dayExercises->flatMap(function ($dayExercise) use ($nextDayExercises) {
                $nextDayExerciseId = $nextDayExercises->get($dayExercise->exercise_id)?->id;

                if (! $nextDayExerciseId) return null;

                return $dayExercise->sets->map(fn($set) => [
                    'day_exercise_id' => $nextDayExercises->get($dayExercise->exercise_id)->id,
                    'target_weight'  => $set->weight,
                    'target_reps'    => $set->reps,
                    'finished_at'    => null,
                ])->toArray();
            })->toArray();

            ExerciseSet::whereIn('day_exercise_id', $nextDayExercises->pluck('id'))->delete();

            ExerciseSet::insert($nextDayTargets);
        });
    }

    public function shouldQueue($event): bool
    {
        $day = MesoDay::with(['mesocycle:id,weeks_duration'])->select('id', 'week', 'mesocycle_id')->find($event->dayId);

        return $day && $day->mesocycle->weeks_duration > $day->week;
    }
}
