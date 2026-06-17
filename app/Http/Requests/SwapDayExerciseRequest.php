<?php

namespace App\Http\Requests;

use App\Models\MesoDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SwapDayExerciseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $day = $this->route('day');

        if (! $day instanceof MesoDay) {
            return false;
        }

        return Gate::allows('owns', $day->mesocycle);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'day_exercise_id' => ['required', 'exists:day_exercises,id'],
            'new_exercise_id' => ['required', 'exists:exercises,id'],
        ];
    }
}
