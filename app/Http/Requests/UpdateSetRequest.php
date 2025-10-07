<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->route()->set->load('dayExercise.day.mesocycle:id,user_id');

        return Gate::allows('update', $this->route()->set);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reps'      => ['required', 'integer', 'max:64'],
            'weight'    => ['required', 'string', 'max:8'],
            'status'    => ['nullable', 'boolean'],
        ];
    }
}
