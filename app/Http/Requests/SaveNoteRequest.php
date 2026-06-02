<?php

namespace App\Http\Requests;

use App\Models\MesoDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SaveNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MesoDay $day */
        $day = $this->route('day');

        $day->loadMissing('mesocycle');

        return Gate::allows('owns', $day->mesocycle);
    }

    public function rules(): array
    {
        $dayId = $this->route('day')?->id;

        return [
            'day_exercise_id' => [
                'required',
                'integer',
                Rule::exists('day_exercises', 'id')->where('meso_day_id', $dayId),
            ],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
