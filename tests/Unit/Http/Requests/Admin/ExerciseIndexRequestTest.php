<?php

namespace Tests\Unit\Http\Requests\Admin;

use App\Enums\EquipmentsEnum;
use App\Http\Requests\Admin\ExerciseIndexRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use PHPUnit\Framework\Attributes\DataProvider;

class ExerciseIndexRequestTest extends TestCase
{
    public function test_valid_query_parameters_pass_validation(): void
    {
        $validator = $this->validator([
            'sort' => 'muscle_group',
            'direction' => 'asc',
            'filter' => [
                'id' => '42',
                'muscle_group' => '3',
                'name' => 'Press',
                'user' => '7',
                'exercise_type' => EquipmentsEnum::DUMBBELL->value,
                'youtube_id' => 'abc123',
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function sortableFieldsProvider(): array
    {
        return [
            'id' => ['id'],
            'muscle group' => ['muscle_group'],
            'name' => ['name'],
            'user' => ['user'],
            'exercise type' => ['exercise_type'],
            'created at' => ['created_at'],
        ];
    }

    #[DataProvider('sortableFieldsProvider')]
    public function test_supported_sort_fields_pass_validation(string $sort): void
    {
        $this->assertTrue($this->validator(['sort' => $sort])->passes());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidQueryParametersProvider(): array
    {
        return [
            'unsupported sort field' => [
                ['sort' => 'description'],
                'sort',
            ],
            'uppercase direction' => [
                ['direction' => 'ASC'],
                'direction',
            ],
            'unsupported filter field' => [
                ['filter' => ['description' => 'Squat']],
                'filter',
            ],
            'non-integer id' => [
                ['filter' => ['id' => 'abc']],
                'filter.id',
            ],
            'non-integer muscle group' => [
                ['filter' => ['muscle_group' => 'chest']],
                'filter.muscle_group',
            ],
            'non-string name' => [
                ['filter' => ['name' => ['Squat']]],
                'filter.name',
            ],
            'non-integer user' => [
                ['filter' => ['user' => 'john']],
                'filter.user',
            ],
            'invalid exercise type' => [
                ['filter' => ['exercise_type' => 'kettlebell']],
                'filter.exercise_type',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     */
    #[DataProvider('invalidQueryParametersProvider')]
    public function test_invalid_query_parameters_fail_validation(array $query, string $field): void
    {
        $validator = $this->validator($query);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($field, $validator->errors()->toArray());
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function validator(array $query): Validator
    {
        $request = new ExerciseIndexRequest;

        return ValidatorFacade::make($query, $request->rules(), $request->messages());
    }
}
