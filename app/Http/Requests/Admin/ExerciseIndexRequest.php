<?php

namespace App\Http\Requests\Admin;

use App\Enums\EquipmentsEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExerciseIndexRequest extends FormRequest
{
    private const array SORT_FIELDS = [
        'id',
        'muscle_group',
        'name',
        'user',
        'exercise_type',
        'created_at',
    ];

    private const array FILTER_FIELDS = [
        'id',
        'muscle_group',
        'name',
        'user',
        'exercise_type',
        'youtube_id',
    ];

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
            'sort' => ['sometimes', 'string', Rule::in(self::SORT_FIELDS)],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'filter' => ['sometimes', 'array:'.implode(',', self::FILTER_FIELDS)],
            'filter.id' => ['sometimes', 'integer'],
            'filter.muscle_group' => ['sometimes', 'integer'],
            'filter.user' => ['sometimes', 'integer'],
            'filter.exercise_type' => ['sometimes', Rule::enum(EquipmentsEnum::class)],
            'filter.youtube_id' => ['sometimes', 'string', 'max:255'],
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
            'sort.in' => 'The selected sort field is invalid.',
            'direction.in' => 'The direction must be asc or desc.',
            'filter.array' => 'The filter contains an unsupported field.',
        ];
    }
}
