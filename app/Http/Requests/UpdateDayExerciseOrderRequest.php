<?php

namespace App\Http\Requests;

use App\Models\MesoDay;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDayExerciseOrderRequest extends FormRequest
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

        $day->loadMissing('mesocycle');

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
            'order' => ['required', 'array', 'distinct', 'list'],
            'order.*' => ['required', 'integer'],
        ];
    }
}
