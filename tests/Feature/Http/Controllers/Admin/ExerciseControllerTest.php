<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\EquipmentsEnum;
use App\Enums\RolesEnum;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExerciseControllerTest extends TestCase
{
    public function test_admin_can_view_a_filtered_exercise_listing(): void
    {
        Exercise::query()->delete();

        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        $muscleGroup = MuscleGroup::factory()->create(['name' => 'Test muscle group']);
        $exercise = Exercise::factory()->create([
            'muscle_group_id' => $muscleGroup->id,
            'exercise_type' => EquipmentsEnum::DUMBBELL->value,
        ]);
        Exercise::factory()->create([
            'exercise_type' => EquipmentsEnum::BARBELL->value,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.exercises.view', [
                'filter' => ['exercise_type' => EquipmentsEnum::DUMBBELL->value],
                'sort' => 'id',
                'direction' => 'asc',
            ]))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Exercises/Index')
                    ->has('exercises.data', 1)
                    ->where('exercises.data.0.id', $exercise->id)
                    ->where('exercises.data.0.muscle_group.name', $muscleGroup->name)
                    ->has('exerciseTypes', count(EquipmentsEnum::cases())),
            );
    }

    public function test_admin_can_create_an_exercise(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        $muscleGroup = MuscleGroup::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.exercises.store'), [
                'name' => 'Incline Press (Paused)',
                'muscle_group_id' => $muscleGroup->id,
                'exercise_type' => EquipmentsEnum::DUMBBELL->value,
                'youtube_id' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10',
            ])
            ->assertRedirect(route('admin.exercises.view'))
            ->assertSessionHas('success', 'Exercise created');

        $this->assertDatabaseHas('exercises', [
            'name' => 'Incline Press (Paused)',
            'muscle_group_id' => $muscleGroup->id,
            'user_id' => null,
            'exercise_type' => EquipmentsEnum::DUMBBELL->value,
            'youtube_id' => 'dQw4w9WgXcQ',
        ]);
    }

    public function test_youtube_id_accepts_a_canonical_video_id(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        $muscleGroup = MuscleGroup::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.exercises.store'), [
                'name' => 'Incline Press',
                'muscle_group_id' => $muscleGroup->id,
                'exercise_type' => EquipmentsEnum::DUMBBELL->value,
                'youtube_id' => 'dQw4w9WgXcQ',
            ])
            ->assertRedirect(route('admin.exercises.view'));

        $this->assertDatabaseHas('exercises', [
            'name' => 'Incline Press',
            'youtube_id' => 'dQw4w9WgXcQ',
        ]);
    }

    public function test_unsupported_youtube_references_are_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        $muscleGroup = MuscleGroup::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.exercises.store'), [
                'name' => 'Incline Press',
                'muscle_group_id' => $muscleGroup->id,
                'exercise_type' => EquipmentsEnum::DUMBBELL->value,
                'youtube_id' => 'https://example.com/watch?v=dQw4w9WgXcQ',
            ])
            ->assertSessionHasErrors([
                'youtube_id' => 'The YouTube ID must be an 11-character video ID or a supported YouTube URL.',
            ]);

        $this->assertDatabaseMissing('exercises', [
            'name' => 'Incline Press',
        ]);
    }

    public function test_an_eleven_character_youtube_id_must_use_valid_characters(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        $muscleGroup = MuscleGroup::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.exercises.store'), [
                'name' => 'Incline Press',
                'muscle_group_id' => $muscleGroup->id,
                'exercise_type' => EquipmentsEnum::DUMBBELL->value,
                'youtube_id' => 'invalid id!',
            ])
            ->assertSessionHasErrors(['youtube_id']);

        $this->assertDatabaseMissing('exercises', [
            'name' => 'Incline Press',
        ]);
    }

    public function test_required_exercise_fields_are_validated(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);

        $this->actingAs($admin)
            ->post(route('admin.exercises.store'))
            ->assertSessionHasErrors(['name', 'muscle_group_id', 'exercise_type']);
    }

    public function test_exercise_fields_must_satisfy_the_store_contract(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);

        $this->actingAs($admin)
            ->post(route('admin.exercises.store'), [
                'name' => 'Press, Version 2',
                'muscle_group_id' => 999999,
                'exercise_type' => 'invalid-equipment',
                'youtube_id' => str_repeat('x', 256),
            ])
            ->assertSessionHasErrors([
                'name',
                'muscle_group_id',
                'exercise_type',
                'youtube_id',
            ]);
    }

    public function test_exercise_name_cannot_exceed_the_database_column_length(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        $muscleGroup = MuscleGroup::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.exercises.store'), [
                'name' => str_repeat('A', 256),
                'muscle_group_id' => $muscleGroup->id,
                'exercise_type' => EquipmentsEnum::DUMBBELL->value,
            ])
            ->assertSessionHasErrors(['name']);
    }

    public function test_youtube_id_is_nullable(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        $muscleGroup = MuscleGroup::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.exercises.store'), [
                'name' => 'Incline Press',
                'muscle_group_id' => $muscleGroup->id,
                'exercise_type' => EquipmentsEnum::DUMBBELL->value,
                'youtube_id' => null,
            ])
            ->assertRedirect(route('admin.exercises.view'));

        $this->assertDatabaseHas('exercises', [
            'name' => 'Incline Press',
            'youtube_id' => null,
        ]);
    }

    public function test_non_admin_cannot_create_an_exercise(): void
    {
        $user = User::factory()->create();
        $muscleGroup = MuscleGroup::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.exercises.view'))
            ->post(route('admin.exercises.store'), [
                'name' => 'Incline Press',
                'muscle_group_id' => $muscleGroup->id,
                'exercise_type' => EquipmentsEnum::DUMBBELL->value,
            ])
            ->assertRedirect(route('admin.exercises.view'))
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseMissing('exercises', [
            'name' => 'Incline Press',
            'muscle_group_id' => $muscleGroup->id,
        ]);
    }
}
