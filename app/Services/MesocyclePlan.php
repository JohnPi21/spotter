<?php

namespace App\Services;

use App\Data\Mesocycle\CreateMesocycleData;
use App\Enums\UnitsOfMeasure;
use App\Models\Exercise;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

class MesocyclePlan
{
    public function fromData(CreateMesocycleData $data): array
    {
        return $data->transform(TransformationContextFactory::create()->withoutPropertyNameMapping());
    }

    /**
     * @return array{schema: array, source: string, week: int|null}
     */
    public function resolve(Mesocycle $mesocycle): array
    {
        if ($mesocycle->schema !== null) {
            return ['schema' => $this->validate($mesocycle->schema), 'source' => 'mesocycle', 'week' => null];
        }

        $mesocycle->loadMissing('mesoTemplate');
        $template = $mesocycle->mesoTemplate;

        if (is_array($template?->schema)) {
            try {
                $schema = $this->validate(array_replace($template->schema, [
                    'name' => $mesocycle->name,
                    'unit' => $mesocycle->unit,
                    'weeksDuration' => $mesocycle->weeks_duration,
                ]));

                return ['schema' => $schema, 'source' => 'template', 'week' => null];
            } catch (ValidationException) {
            }
        }

        return $this->reconstruct($mesocycle);
    }

    public function validate(mixed $schema): array
    {
        if (! is_array($schema)) {
            throw ValidationException::withMessages(['schema' => 'The saved plan is not a valid schema.']);
        }

        return Validator::make($schema, [
            'name' => ['required', 'string'],
            'unit' => ['sometimes', Rule::enum(UnitsOfMeasure::class)],
            'weeksDuration' => ['required', 'integer', 'between:3,12'],
            'days' => ['required', 'array', 'min:1', 'max:7'],
            'days.*.label' => ['required', 'string', 'max:64'],
            'days.*.exercises' => ['required', 'array', 'min:1', 'max:32'],
            'days.*.exercises.*.muscleGroup' => ['required', 'integer', 'exists:muscle_groups,id'],
            'days.*.exercises.*.exerciseId' => ['required', 'integer', 'exists:exercises,id'],
            'days.*.exercises.*.oneRepMax' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:2048'],
            'days.*.exercises.*.sets' => ['sometimes', 'array'],
            'days.*.exercises.*.sets.*' => ['array'],
            'days.*.exercises.*.sets.*.minReps' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'days.*.exercises.*.sets.*.maxReps' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'days.*.exercises.*.sets.*.minRir' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'days.*.exercises.*.sets.*.maxRir' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'days.*.exercises.*.sets.*.percentOneRepMax' => ['sometimes', 'nullable', 'integer', 'between:0,255'],
        ])->validate();
    }

    /**
     * @return array{muscle_groups_count: int, sets_per_week: int, muscle_groups: list<array{id: int, name: string, sets: int}>}
     */
    public function stats(array $schema): array
    {
        $plannedExercises = collect($schema['days'])->flatMap(fn (array $day) => $day['exercises']);
        $exercises = Exercise::query()->with('muscleGroup')->whereIn('id', $plannedExercises->pluck('exerciseId'))->get()->keyBy('id');
        $groups = [];

        foreach ($plannedExercises as $plannedExercise) {
            $group = $exercises->get($plannedExercise['exerciseId'])?->muscleGroup;

            if (! $group) {
                throw ValidationException::withMessages(['schema' => 'An exercise or its muscle group is missing.']);
            }

            $groups[$group->id] ??= ['id' => $group->id, 'name' => $group->name, 'sets' => 0];
            $groups[$group->id]['sets'] += max(1, count($plannedExercise['sets'] ?? []));
        }

        return [
            'muscle_groups_count' => count($groups),
            'sets_per_week' => array_sum(array_column($groups, 'sets')),
            'muscle_groups' => array_values($groups),
        ];
    }

    /**
     * @return array{schema: array, source: string, week: int}
     */
    private function reconstruct(Mesocycle $mesocycle): array
    {
        $mesocycle->loadMissing(['days.dayExercises.exercise', 'days.dayExercises.sets']);
        $weeks = $mesocycle->days->groupBy('week')->sortKeysDesc();
        $week = $weeks->first(fn ($days) => $days->count() === $mesocycle->days_per_week
            && $days->pluck('day_order')->sort()->values()->all() === range(1, $mesocycle->days_per_week)
            && $days->every(fn (MesoDay $day) => $day->isFinished()));
        $week ??= $weeks->get($mesocycle->weeks_duration);

        if (! $week || $week->count() !== $mesocycle->days_per_week
            || $week->pluck('day_order')->sort()->values()->all() !== range(1, $mesocycle->days_per_week)) {
            throw ValidationException::withMessages(['schema' => 'No complete weekly structure is available to reconstruct this plan.']);
        }

        $days = [];

        foreach ($week->sortBy('day_order') as $day) {
            $exercises = [];

            foreach ($day->dayExercises->sortBy('position') as $dayExercise) {
                if (! $dayExercise->exercise) {
                    throw ValidationException::withMessages(['schema' => 'A workout exercise no longer exists.']);
                }

                $exercises[] = [
                    'muscleGroup' => $dayExercise->exercise->muscle_group_id,
                    'exerciseId' => $dayExercise->exercise_id,
                    'oneRepMax' => $dayExercise->one_rep_max,
                    'sets' => $dayExercise->sets->sortBy('id')->map(fn ($set) => [
                        'minReps' => $set->min_reps,
                        'maxReps' => $set->max_reps,
                        'minRir' => $set->min_rir,
                        'maxRir' => $set->max_rir,
                        'percentOneRepMax' => $set->percent_one_rep_max,
                    ])->values()->all(),
                ];
            }

            $days[] = ['label' => $day->label, 'exercises' => $exercises];
        }

        return [
            'schema' => $this->validate([
                'name' => $mesocycle->name,
                'unit' => $mesocycle->unit,
                'weeksDuration' => $mesocycle->weeks_duration,
                'days' => $days,
            ]),
            'source' => 'workouts',
            'week' => $week->first()->week,
        ];
    }
}
