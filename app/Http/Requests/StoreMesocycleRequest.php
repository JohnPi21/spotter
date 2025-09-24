<?php

namespace App\Http\Requests;

use App\Enums\UnitOfMeasure;
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                              => ['required', 'string'],
            'unit'                              => ['sometimes', Rule::enum(UnitOfMeasure::class)],
            'weeksDuration'                     => ['required', 'integer', 'min:3', 'max:12'],
            'days'                              => ['required', 'array', 'min:1', 'max:7'],
            'days.*.label'                      => ['required', 'string', 'min:1', 'max:64'],
            'days.*.exercises'                  => ['required', 'array', 'min:1', 'max:32'],
            'days.*.exercises.*.muscleGroup'    => ['required', 'integer', 'min:1', 'exists:muscle_groups,id'],
            'days.*.exercises.*.exerciseID'     => ['required', 'integer', 'min:1', 'exists:exercises,id']
        ];
    }
}
