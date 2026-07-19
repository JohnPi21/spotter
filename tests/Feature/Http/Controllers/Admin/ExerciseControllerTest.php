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
}
