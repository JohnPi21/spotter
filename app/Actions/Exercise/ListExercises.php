<?php

namespace App\Actions\Exercise;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListExercises
{
    private const array SORT_COLUMNS = [
        'id' => 'id',
        'exercise_type' => 'exercise_type',
        'created_at' => 'created_at',
    ];

    /**
     * @param  array{
     *     sort?: string,
     *     direction?: string,
     *     filter?: array{
     *         id?: int|string,
     *         muscle_group?: int|string,
     *         user?: int|string,
     *         exercise_type?: string,
     *         youtube_id?: string
     *     }
     * }  $parameters
     * @return LengthAwarePaginator<int, Exercise>
     */
    public function execute(array $parameters): LengthAwarePaginator
    {
        $query = Exercise::query()->with([
            'muscleGroup:id,name',
            'user:id,name,email',
        ]);

        $this->applyFilters($query, $parameters['filter'] ?? []);
        $this->applySorting(
            $query,
            $parameters['sort'] ?? 'id',
            $parameters['direction'] ?? 'desc',
        );

        return $query->paginate()->withQueryString();
    }

    /**
     * @param  Builder<Exercise>  $query
     */
    private function applySorting(Builder $query, string $sort, string $direction): void
    {
        $column = match ($sort) {
            'muscle_group' => MuscleGroup::query()
                ->select('name')
                ->whereColumn('muscle_groups.id', 'exercises.muscle_group_id'),
            'user' => User::query()
                ->select('name')
                ->whereColumn('users.id', 'exercises.user_id'),
            default => self::SORT_COLUMNS[$sort],
        };

        $query->orderBy($column, $direction);

        if ($sort !== 'id') {
            $query->orderBy('exercises.id', $direction);
        }
    }

    /**
     * @param  Builder<Exercise>  $query
     * @param  array{
     *     id?: int|string,
     *     muscle_group?: int|string,
     *     user?: int|string,
     *     exercise_type?: string,
     *     youtube_id?: string
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
                array_key_exists('muscle_group', $filters),
                fn (Builder $query) => $query->where('muscle_group_id', $filters['muscle_group']),
            )
            ->when(
                array_key_exists('user', $filters),
                fn (Builder $query) => $query->where('user_id', $filters['user']),
            )
            ->when(
                array_key_exists('exercise_type', $filters),
                fn (Builder $query) => $query->where('exercise_type', $filters['exercise_type']),
            )
            ->when(
                array_key_exists('youtube_id', $filters),
                fn (Builder $query) => $query->where('youtube_id', 'like', '%'.$filters['youtube_id'].'%'),
            );
    }
}
