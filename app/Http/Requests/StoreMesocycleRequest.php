<?php

namespace App\Http\Requests;

use App\Enums\UnitsOfMeasure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMesocycleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $days = collect($this->input('days', []));

        $exercisesIds = $days->flatMap->exercises->pluck('exerciseId')->filter()->unique()->toArray();
        $muscleGroupsIds = $days->flatMap->exercises->pluck('muscleGroup')->filter()->unique()->toArray();

        $this->merge([
            'exercisesIds' => $exercisesIds,
            'muscleGroupsIds' => $muscleGroupsIds,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'unit' => ['sometimes', Rule::enum(UnitsOfMeasure::class)],
            'weeksDuration' => ['required', 'integer', 'min:3', 'max:12'],

            'days' => ['required', 'array', 'min:1', 'max:7'],
            'days.*.label' => ['required', 'string', 'min:1', 'max:64'],

            'days.*.exercises' => ['required', 'array', 'min:1', 'max:32'],
            'days.*.exercises.*.muscleGroup' => ['required', 'integer', 'min:1'],
            'days.*.exercises.*.exerciseId' => ['required', 'integer', 'min:1'],
            'days.*.exercises.*.oneRepMax' => ['sometimes', 'nullable', 'decimal:0,3', 'max:2048'],

            'days.*.exercises.*.sets' => ['sometimes'],
            'days.*.exercises.*.sets.*.minReps' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'days.*.exercises.*.sets.*.maxReps' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'days.*.exercises.*.sets.*.minRir' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'days.*.exercises.*.sets.*.maxRir' => ['sometimes', 'nullable', 'integer', 'min:0'],

            'exercisesIds' => [Rule::exists('exercises', 'id')],
            'muscleGroupsIds' => [Rule::exists('muscle_groups', 'id')],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'days.required' => __('mesocycle.days_required'),
            'days.*.exercises.required' => __('mesocycle.exercises_required'),
            'days.*.exercises.*.exerciseId.required' => __('mesocycle.exercise_required'),
            'days.*.exercises.*.exerciseId.min' => __('mesocycle.exercise_required'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'days.*.label' => __('mesocycle.attr.day_label'),
            'days.*.exercises' => __('mesocycle.attr.exercises'),
            'days.*.exercises.*.exerciseId' => __('mesocycle.attr.exercise'),
            'days.*.exercises.*.muscleGroup' => __('mesocycle.attr.muscle_group'),
        ];
    }
}
