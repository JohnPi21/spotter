<?php

namespace Tests\Unit\Http\Requests\Admin;

use App\Http\Requests\Admin\UserIndexRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use PHPUnit\Framework\Attributes\DataProvider;

class UserIndexRequestTest extends TestCase
{
    public function test_valid_query_parameters_pass_validation(): void
    {
        $validator = $this->validator([
            'sort' => 'created_at',
            'direction' => 'desc',
            'filter' => [
                'id' => '42',
                'name' => 'John',
                'email' => 'john@',
                'verified' => '1',
                'created_at' => '2026-07-19',
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
            'name' => ['name'],
            'email' => ['email'],
            'verified' => ['verified'],
            'created at' => ['created_at'],
        ];
    }

    #[DataProvider('sortableFieldsProvider')]
    public function test_supported_sort_fields_pass_validation(string $sort): void
    {
        $validator = $this->validator(['sort' => $sort]);

        $this->assertTrue($validator->passes());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidQueryParametersProvider(): array
    {
        return [
            'unsupported sort field' => [
                ['sort' => 'password'],
                'sort',
            ],
            'uppercase direction' => [
                ['direction' => 'ASC'],
                'direction',
            ],
            'unsupported direction' => [
                ['direction' => 'sideways'],
                'direction',
            ],
            'unsupported filter field' => [
                ['filter' => ['password' => 'secret']],
                'filter',
            ],
            'non-integer id' => [
                ['filter' => ['id' => 'abc']],
                'filter.id',
            ],
            'non-alpha name' => [
                ['filter' => ['name' => 'John Doe']],
                'filter.name',
            ],
            'invalid verified value' => [
                ['filter' => ['verified' => 'yes']],
                'filter.verified',
            ],
            'invalid created at date' => [
                ['filter' => ['created_at' => 'not-a-date']],
                'filter.created_at',
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
        $request = new UserIndexRequest;

        return ValidatorFacade::make($query, $request->rules(), $request->messages());
    }
}
