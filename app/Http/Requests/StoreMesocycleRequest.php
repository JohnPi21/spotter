<?php

namespace App\Http\Requests;

use App\Enums\UnitsOfMeasure;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

        $exercisesIds = $days->flatMap->exercises->pluck('exerciseID')->filter()->unique()->toArray();
        $muscleGroupsIds = $days->flatMap->exercises->pluck('muscleGroup')->filter()->unique()->toArray();

        $this->merge([
            'exercisesIds' => $exercisesIds,
            'muscleGroupsIds' => $muscleGroupsIds
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
            'name'                              => ['required', 'string'],
            'unit'                              => ['sometimes', Rule::enum(UnitsOfMeasure::class)],
            'weeksDuration'                     => ['required', 'integer', 'min:3', 'max:12'],
            'days'                              => ['required', 'array', 'min:1', 'max:7'],
            'days.*.label'                      => ['required', 'string', 'min:1', 'max:64'],
            'days.*.exercises'                  => ['required', 'array', 'min:1', 'max:32'],
            'days.*.exercises.*.muscleGroup'    => ['required', 'integer', 'min:1'],
            'days.*.exercises.*.exerciseID'     => ['required', 'integer', 'min:1'],
            'exercisesIds'                      => [Rule::exists('exercises', 'id')],
            'muscleGroupsIds'                   => [Rule::exists('muscle_groups', 'id')],
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
            'days.required'                          => __('mesocycle.days_required'),
            'days.*.exercises.required'              => __('mesocycle.exercises_required'),
            'days.*.exercises.*.exerciseID.required' => __('mesocycle.exercise_required'),
            'days.*.exercises.*.exerciseID.min'       => __('mesocycle.exercise_required'),
        ];
    }

    /**
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'days.*.label'                          => __('mesocycle.attr.day_label'),
            'days.*.exercises'                      => __('mesocycle.attr.exercises'),
            'days.*.exercises.*.exerciseID'         => __('mesocycle.attr.exercise'),
            'days.*.exercises.*.muscleGroup'        => __('mesocycle.attr.muscle_group'),
        ];
    }
}
