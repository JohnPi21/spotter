<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ListUsers
{
    private const array SORT_COLUMNS = [
        'id' => 'id',
        'name' => 'name',
        'email' => 'email',
        'verified' => 'email_verified_at',
        'created_at' => 'created_at',
    ];

    /**
     * @param  array{
     *     sort?: string,
     *     direction?: string,
     *     filter?: array{
     *         id?: int|string,
     *         name?: string,
     *         email?: string,
     *         verified?: bool|int|string,
     *         created_at?: string
     *     }
     * }  $parameters
     * @return LengthAwarePaginator<int, User>
     */
    public function execute(array $parameters): LengthAwarePaginator
    {
        $query = User::query();

        $this->applyFilters($query, $parameters['filter'] ?? []);
        $this->applySorting(
            $query,
            $parameters['sort'] ?? 'id',
            $parameters['direction'] ?? 'desc',
        );

        return $query->paginate()->withQueryString();
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applySorting(Builder $query, ?string $sort, string $direction): void
    {
        $query->orderBy(self::SORT_COLUMNS[$sort], $direction);
    }

    /**
     * @param  Builder<User>  $query
     * @param  array{
     *     id?: int|string,
     *     name?: string,
     *     email?: string,
     *     verified?: bool|int|string,
     *     created_at?: string
     * }  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when(
                array_key_exists('id', $filters),
                fn (Builder $query) => $query->whereKey($filters['id']),
            )
            ->when(
                array_key_exists('name', $filters),
                fn (Builder $query) => $query->where('name', 'like', '%'.$filters['name'].'%'),
            )
            ->when(
                array_key_exists('email', $filters),
                fn (Builder $query) => $query->where('email', 'like', '%'.$filters['email'].'%'),
            )
            ->when(
                array_key_exists('verified', $filters),
                function (Builder $query) use ($filters): void {
                    if ((bool) $filters['verified']) {
                        $query->whereNotNull('email_verified_at');

                        return;
                    }

                    $query->whereNull('email_verified_at');
                },
            )
            ->when(
                array_key_exists('created_at', $filters),
                fn (Builder $query) => $query->whereDate('created_at', $filters['created_at']),
            );
    }
}
