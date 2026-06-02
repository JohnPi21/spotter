<?php

namespace App\Http\Requests;

use App\Models\ExerciseSet;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $set = $this->route('set');

        if (! $set instanceof ExerciseSet) {
            return false;
        }

        $set->load('dayExercise.day.mesocycle:id,user_id');

        return Gate::allows('update', $set);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reps' => ['required', 'integer', 'max:64'],
            'weight' => ['required', 'decimal:0,3', 'max:2048'],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
