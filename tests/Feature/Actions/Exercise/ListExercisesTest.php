<?php

namespace Tests\Feature\Actions\Exercise;

use App\Actions\Exercise\ListExercises;
use App\Enums\EquipmentsEnum;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\User;
use Tests\TestCase;

class ListExercisesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Exercise::query()->delete();
    }

    public function test_it_filters_exercises_by_id(): void
    {
        $matchingExercise = $this->createExercise();
        $this->createExercise();

        $exercises = (new ListExercises)->execute([
            'filter' => ['id' => (string) $matchingExercise->id],
        ]);

        $this->assertSame([$matchingExercise->id], $exercises->pluck('id')->all());
    }

    public function test_it_filters_exercises_by_muscle_group_id(): void
    {
        $matchingMuscleGroup = MuscleGroup::factory()->create();
        $otherMuscleGroup = MuscleGroup::factory()->create();
        $matchingExercise = $this->createExercise(['muscle_group_id' => $matchingMuscleGroup->id]);
        $this->createExercise(['muscle_group_id' => $otherMuscleGroup->id]);

        $exercises = (new ListExercises)->execute([
            'filter' => ['muscle_group' => (string) $matchingMuscleGroup->id],
        ]);

        $this->assertSame([$matchingExercise->id], $exercises->pluck('id')->all());
    }

    public function test_it_filters_exercises_by_user_id(): void
    {
        $matchingUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $matchingExercise = $this->createExercise(['user_id' => $matchingUser->id]);
        $this->createExercise(['user_id' => $otherUser->id]);

        $exercises = (new ListExercises)->execute([
            'filter' => ['user' => (string) $matchingUser->id],
        ]);

        $this->assertSame([$matchingExercise->id], $exercises->pluck('id')->all());
    }

    public function test_it_filters_exercises_by_exercise_type(): void
    {
        $matchingExercise = $this->createExercise(['exercise_type' => EquipmentsEnum::DUMBBELL->value]);
        $this->createExercise(['exercise_type' => EquipmentsEnum::BARBELL->value]);

        $exercises = (new ListExercises)->execute([
            'filter' => ['exercise_type' => EquipmentsEnum::DUMBBELL->value],
        ]);

        $this->assertSame([$matchingExercise->id], $exercises->pluck('id')->all());
    }

    public function test_it_filters_exercises_by_partial_youtube_id(): void
    {
        $matchingExercise = $this->createExercise(['youtube_id' => 'prefix-video-code-suffix']);
        $this->createExercise(['youtube_id' => 'another-video']);

        $exercises = (new ListExercises)->execute([
            'filter' => ['youtube_id' => 'video-code'],
        ]);

        $this->assertSame([$matchingExercise->id], $exercises->pluck('id')->all());
    }

    public function test_it_sorts_exercises_by_muscle_group_name(): void
    {
        $chest = MuscleGroup::factory()->create(['name' => 'Chest']);
        $legs = MuscleGroup::factory()->create(['name' => 'Legs']);
        $this->createExercise(['name' => 'Squat', 'muscle_group_id' => $legs->id]);
        $this->createExercise(['name' => 'Bench press', 'muscle_group_id' => $chest->id]);

        $exercises = (new ListExercises)->execute([
            'sort' => 'muscle_group',
            'direction' => 'asc',
        ]);

        $this->assertSame(['Bench press', 'Squat'], $exercises->pluck('name')->all());
    }

    public function test_it_sorts_exercises_by_user_name(): void
    {
        $alice = User::factory()->create(['name' => 'Alice']);
        $charlie = User::factory()->create(['name' => 'Charlie']);
        $this->createExercise(['name' => 'Charlie exercise', 'user_id' => $charlie->id]);
        $this->createExercise(['name' => 'Alice exercise', 'user_id' => $alice->id]);

        $exercises = (new ListExercises)->execute([
            'sort' => 'user',
            'direction' => 'asc',
        ]);

        $this->assertSame(['Alice exercise', 'Charlie exercise'], $exercises->pluck('name')->all());
    }

    public function test_it_uses_id_descending_as_the_default_sort(): void
    {
        $first = $this->createExercise();
        $second = $this->createExercise();

        $exercises = (new ListExercises)->execute([]);

        $this->assertSame([$second->id, $first->id], $exercises->pluck('id')->all());
    }

    public function test_it_sorts_exercises_by_exercise_type(): void
    {
        $dumbbell = $this->createExercise(['exercise_type' => EquipmentsEnum::DUMBBELL->value]);
        $barbell = $this->createExercise(['exercise_type' => EquipmentsEnum::BARBELL->value]);

        $exercises = (new ListExercises)->execute([
            'sort' => 'exercise_type',
            'direction' => 'asc',
        ]);

        $this->assertSame([$barbell->id, $dumbbell->id], $exercises->pluck('id')->all());
    }

    public function test_it_sorts_exercises_by_created_at(): void
    {
        $newer = $this->createExercise(['created_at' => '2026-07-19 12:00:00']);
        $older = $this->createExercise(['created_at' => '2026-07-18 12:00:00']);

        $exercises = (new ListExercises)->execute([
            'sort' => 'created_at',
            'direction' => 'asc',
        ]);

        $this->assertSame([$older->id, $newer->id], $exercises->pluck('id')->all());
    }

    public function test_it_eager_loads_listing_relationships(): void
    {
        $this->createExercise(['user_id' => User::factory()]);

        $exercise = (new ListExercises)->execute([])->first();

        $this->assertTrue($exercise->relationLoaded('muscleGroup'));
        $this->assertTrue($exercise->relationLoaded('user'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createExercise(array $attributes = []): Exercise
    {
        return Exercise::factory()->create([
            'exercise_type' => EquipmentsEnum::BARBELL->value,
            ...$attributes,
        ]);
    }
}
