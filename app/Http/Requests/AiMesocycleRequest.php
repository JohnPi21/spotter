<?php

namespace App\Http\Requests;

use App\Enums\EquipmentsEnum;
use App\Enums\ExperienceEnum;
use App\Enums\SessionDurationEnum;
use App\Enums\SplitsEnum;
use App\Enums\TrainingGoalsEnum;
use App\Enums\UnitsOfMeasure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiMesocycleRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],

            'unit' => ['required', 'string', Rule::enum(UnitsOfMeasure::class)],

            'weeksDuration' => ['required', 'integer', 'min:3', 'max:12'],
            'daysPerWeek' => ['required', 'integer', 'min:1', 'max:7'],

            // must be one of sessionDurationOptions [30, 45, 60, 75, 90, 120]
            'sessionDuration' => [
                'required',
                'integer',
                Rule::enum(SessionDurationEnum::class),
            ],

            // primaryGoal: "hypertrophy" | "strength" | "fat_loss" | "recomp"
            'primaryGoal' => [
                'required',
                Rule::enum(TrainingGoalsEnum::class),
            ],

            // splitPreference: "full_body" | "upper_lower" | "push_pull_legs" | "bro_split" | "custom"
            'splitPreference' => [
                'required',
                Rule::enum(SplitsEnum::class),
            ],

            // experienceLevel: "beginner" | "intermediate" | "advanced"
            'experienceLevel' => [
                'required',
                Rule::enum(ExperienceEnum::class),
            ],

            // equipment: string[]
            'equipment' => ['nullable', 'array'],
            'equipment.*' => [
                Rule::enum(EquipmentsEnum::class),
            ],

            'injuries' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
