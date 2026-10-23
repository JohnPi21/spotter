<?php

namespace Tests\Feature\Seeders;

use App\Enums\RolesEnum;
use App\Models\Mesocycle;
use App\Models\User;
use Database\Seeders\AdminMesocycleSeeder;
use Tests\TestCase;

class AdminMesocycleSeederTest extends TestCase
{
    public function test_it_creates_complete_mesocycles_for_the_existing_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN);
        $other = User::factory()->create();

        $this->seed(AdminMesocycleSeeder::class);

        $mesocycles = $admin->mesocycles()->with('mesoTemplate', 'days.dayExercises.sets')->get();

        $this->assertCount(3, $mesocycles);
        $this->assertSame(0, $other->mesocycles()->count());
        $this->assertDatabaseCount('users', 2);
        $this->assertSame(1, $admin->mesocycles()->active()->count());

        foreach ($mesocycles as $mesocycle) {
            $this->assertNotNull($mesocycle->mesoTemplate);
            $this->assertSame($admin->id, $mesocycle->mesoTemplate->user_id);
            $this->assertCount($mesocycle->totalDays(), $mesocycle->days);

            for ($week = 1; $week <= $mesocycle->weeks_duration; $week++) {
                $days = $mesocycle->days->where('week', $week)->sortBy('day_order')->values();

                $this->assertSame(range(1, $mesocycle->days_per_week), $days->pluck('day_order')->all());

                foreach ($days as $day) {
                    $this->assertNull($day->finished_at);
                    $this->assertNotEmpty($day->dayExercises);

                    foreach ($day->dayExercises as $dayExercise) {
                        $firstWeekExercise = $mesocycle->days->where('week', 1)
                            ->firstWhere('day_order', $day->day_order)
                            ->dayExercises->firstWhere('position', $dayExercise->position);

                        $this->assertSame($firstWeekExercise->exercise_id, $dayExercise->exercise_id);
                        $this->assertCount(3, $dayExercise->sets);

                        foreach ($dayExercise->sets as $set) {
                            $this->assertSame(8, $set->min_reps);
                            $this->assertSame(12, $set->max_reps);
                            $this->assertSame(1, $set->min_rir);
                            $this->assertSame(3, $set->max_rir);
                            $this->assertNull($set->reps);
                            $this->assertNull($set->weight);
                            $this->assertNull($set->finished_at);
                        }
                    }
                }
            }
        }
    }

    public function test_rerunning_preserves_edits_and_the_existing_active_mesocycle(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN);
        $active = Mesocycle::factory()->for($admin)->isActive()->notFinished()->create();

        $this->seed(AdminMesocycleSeeder::class);

        $sample = $admin->mesocycles()->where('name', 'Playground: Full Body')->firstOrFail();
        $sample->update(['notes' => json_encode(['text' => 'Keep my edits'])]);
        $set = $sample->days()->first()->dayExercises()->first()->sets()->first();
        $set->update(['reps' => 10, 'weight' => 50, 'finished_at' => now()]);
        $original = $set->fresh()->getAttributes();

        $this->seed(AdminMesocycleSeeder::class);

        $this->assertSame(4, $admin->mesocycles()->count());
        $this->assertSame($active->id, $admin->mesocycles()->active()->sole()->id);
        $this->assertSame(['text' => 'Keep my edits'], json_decode($sample->fresh()->notes, true));
        $this->assertSame($original, $set->fresh()->getAttributes());
        $this->assertDatabaseCount('meso_templates', 3);
    }

    public function test_it_does_not_create_an_admin_or_seed_another_user_when_admin_is_missing(): void
    {
        User::factory()->create();

        $this->seed(AdminMesocycleSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('mesocycles', 0);
    }
}
