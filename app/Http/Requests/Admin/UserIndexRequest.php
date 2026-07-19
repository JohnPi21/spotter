<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserIndexRequest extends FormRequest
{
    private const array FIELDS = ['id', 'name', 'email', 'verified', 'created_at'];

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
            'sort' => [
                'sometimes',
                'string',
                Rule::in(self::FIELDS),
            ],
            'direction' => [
                'sometimes',
                'string',
                Rule::in(['asc', 'desc']),
            ],
            'filter' => [
                'sometimes',
                'array:'.implode(',', self::FIELDS),
            ],
            'filter.id' => ['sometimes', 'integer'],
            'filter.name' => ['sometimes', 'string', 'alpha'],
            'filter.email' => ['sometimes', 'string'],
            'filter.verified' => ['sometimes', 'boolean'],
            'filter.created_at' => ['sometimes', 'date'],
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
