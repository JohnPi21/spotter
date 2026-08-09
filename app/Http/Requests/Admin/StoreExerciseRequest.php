<?php

namespace App\Http\Requests\Admin;

use App\Enums\EquipmentsEnum;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\YoutubeUrlParser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExerciseRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $videoReference = $this->input('youtube_id');

        if (! is_string($videoReference) || $videoReference === '') {
            return;
        }

        $videoId = app(YoutubeUrlParser::class)->parse($videoReference);

        if ($videoId !== null) {
            $this->merge(['youtube_id' => $videoId]);
        }
    }

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:1',
                'max:255',
                'regex:/\A[A-Za-z() \-]+\z/',
                Rule::unique(Exercise::class),
            ],
            'muscle_group_id' => [
                'required',
                'integer',
                Rule::exists(MuscleGroup::class, 'id'),
            ],
            'exercise_type' => ['required', 'string', Rule::enum(EquipmentsEnum::class)],
            'youtube_id' => ['nullable', 'string', 'size:11', 'regex:/\A[A-Za-z0-9_-]{11}\z/'],
        ];
    }

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The exercise name may only contain letters, spaces, parentheses, and hyphens.',
            'muscle_group_id.exists' => 'The selected muscle group does not exist.',
            'exercise_type.enum' => 'The selected exercise type is invalid.',
            'youtube_id.size' => 'The YouTube ID must be an 11-character video ID or a supported YouTube URL.',
            'youtube_id.regex' => 'The YouTube ID must be an 11-character video ID or a supported YouTube URL.',
        ];
    }
}
