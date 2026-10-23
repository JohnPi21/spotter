<?php

namespace Tests\Feature\Mesocycles;

use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\MesoTemplate;
use App\Models\User;
use App\Services\MesocyclePlan;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MesocyclePlanTest extends TestCase
{
    private function payload(int $sets = 2): array
    {
        $exercise = Exercise::query()->firstOrFail();

        return [
            'name' => 'My plan',
            'unit' => 'kg',
            'weeksDuration' => 3,
            'days' => [[
                'label' => 'Day A',
                'exercises' => [[
                    'muscleGroup' => $exercise->muscle_group_id,
                    'exerciseId' => $exercise->id,
                    'oneRepMax' => null,
                    'sets' => array_fill(0, $sets, [
                        'minReps' => 8, 'maxReps' => 12, 'minRir' => 1, 'maxRir' => 3,
                        'percentOneRepMax' => null,
                    ]),
                ]],
            ]],
        ];
    }

    private function createMesocycle(): Mesocycle
    {
        $this->actingAs(User::factory()->create())
            ->post(route('mesocycles.store'), $this->payload())
            ->assertRedirect(route('mesocycles'))->assertSessionHasNoErrors();

        return Mesocycle::query()->latest('id')->firstOrFail();
    }

    private function legacyMesocycle(): Mesocycle
    {
        $mesocycle = Mesocycle::factory()->notFinished()->create([
            'days_per_week' => 1, 'weeks_duration' => 3,
        ]);
        $exercise = Exercise::query()->firstOrFail();

        for ($week = 1; $week <= 3; $week++) {
            $day = MesoDay::factory()->for($mesocycle)->create([
                'week' => $week, 'day_order' => 1, 'label' => "Week {$week}",
                'finished_at' => $week <= 2 ? now() : null,
            ]);
            $dayExercise = DayExercise::factory()->for($day, 'day')->for($exercise)->create(['position' => 1, 'one_rep_max' => null]);
            ExerciseSet::factory()->for($dayExercise)->count($week)->create([
                'min_reps' => 6, 'max_reps' => 10, 'min_rir' => 1, 'max_rir' => 2,
                'percent_one_rep_max' => null, 'reps' => 9, 'weight' => 80,
            ]);
        }

        return $mesocycle;
    }

    public function test_creation_saves_independent_schemas_and_weekly_stats(): void
    {
        $mesocycle = $this->createMesocycle();
        $template = $mesocycle->mesoTemplate;
        $this->assertSame($mesocycle->schema, $template->schema);
        $this->assertSame($mesocycle->planned_stats, $template->planned_stats);
        $this->assertSame(2, $mesocycle->planned_stats['sets_per_week']);
        $this->assertSame(1, $mesocycle->planned_stats['muscle_groups_count']);
        $this->assertSame(6, ExerciseSet::query()->count());
    }

    public function test_standalone_template_creation_saves_schema_and_stats(): void
    {
        $payload = $this->payload(3);
        app(\App\Actions\Mesocycle\CreateMesoTemplate::class)->execute(
            new \App\Data\Mesocycle\CreateMesocycleTemplateData(
                name: $payload['name'], frequency: 1, schema: json_encode($payload),
                ai_generated: false, user_id: User::factory()->create()->id, ai_request_id: null,
            ),
        );
        $template = MesoTemplate::query()->firstOrFail();
        $this->assertEquals($payload, $template->schema);
        $this->assertSame(3, $template->planned_stats['sets_per_week']);
        $this->assertDatabaseCount('mesocycles', 0);
    }

    public function test_decimal_one_rep_max_and_percentage_prescriptions_survive_an_edit_round_trip(): void
    {
        $mesocycle = $this->legacyMesocycle();
        $exercise = $mesocycle->days()->where('week', 2)->first()->dayExercises()->first();
        $exercise->update(['one_rep_max' => 123.456]);
        $exercise->sets()->update(['percent_one_rep_max' => 75]);
        $schema = app(MesocyclePlan::class)->resolve($mesocycle)['schema'];
        $this->actingAs($mesocycle->user)->patch(route('mesocycles.update', $mesocycle), $schema)
            ->assertSessionHasNoErrors();
        $this->assertEquals(123.456, $mesocycle->fresh()->schema['days'][0]['exercises'][0]['oneRepMax']);
        $nextExercise = $mesocycle->days()->where('week', 3)->first()->dayExercises()->first();
        $this->assertEquals(123.456, $nextExercise->one_rep_max);
        $this->assertSame(75, $nextExercise->sets()->first()->percent_one_rep_max);
    }

    public function test_multiple_blank_sets_are_preserved_in_workouts_and_stats(): void
    {
        $payload = $this->payload(3);
        $payload['days'][0]['exercises'][0]['sets'] = array_fill(0, 3, [
            'minReps' => null, 'maxReps' => null, 'minRir' => null, 'maxRir' => null,
        ]);
        $this->actingAs(User::factory()->create())->post(route('mesocycles.store'), $payload)->assertSessionHasNoErrors();
        $mesocycle = Mesocycle::query()->latest('id')->firstOrFail();
        $this->assertSame(3, $mesocycle->planned_stats['sets_per_week']);
        $this->assertSame(9, ExerciseSet::query()->count());
    }

    public function test_an_exercise_without_defined_sets_has_one_placeholder_set(): void
    {
        $this->actingAs(User::factory()->create())->post(route('mesocycles.store'), $this->payload(0))->assertSessionHasNoErrors();
        $this->assertSame(1, Mesocycle::query()->latest('id')->firstOrFail()->planned_stats['sets_per_week']);
        $this->assertSame(3, ExerciseSet::query()->count());
    }

    public function test_editing_only_changes_this_mesocycle_and_preserves_completed_workouts(): void
    {
        $mesocycle = $this->createMesocycle();
        $template = $mesocycle->mesoTemplate;
        $templateBefore = $template->getAttributes();
        $other = Mesocycle::factory()->create([
            'meso_template_id' => $template->id, 'schema' => $mesocycle->schema,
            'planned_stats' => $mesocycle->planned_stats,
        ]);
        $day = $mesocycle->days()->where('week', 1)->firstOrFail();
        $day->update(['finished_at' => now()]);
        $set = $day->dayExercises()->first()->sets()->first();
        $set->update(['weight' => 80, 'reps' => 9, 'finished_at' => now()]);
        $dayBefore = $day->getAttributes();
        $setBefore = $set->fresh()->getAttributes();
        $payload = $this->payload(4);
        $payload['name'] = 'Personal adaptation';

        $this->patch(route('mesocycles.update', $mesocycle), $payload)->assertSessionHasNoErrors();

        $this->assertSame('Personal adaptation', $mesocycle->fresh()->schema['name']);
        $this->assertSame(4, $mesocycle->fresh()->planned_stats['sets_per_week']);
        $this->assertSame($templateBefore, $template->fresh()->getAttributes());
        $this->assertSame($other->schema, $other->fresh()->schema);
        $this->assertSame($dayBefore, $day->fresh()->getAttributes());
        $this->assertSame($setBefore, $set->fresh()->getAttributes());
        $this->assertSame(4, $mesocycle->days()->where('week', 2)->first()->dayExercises()->first()->sets()->count());
        $this->get(route('mesocycles.edit', $mesocycle))->assertInertia(fn (Assert $page) => $page
            ->where('name', 'Personal adaptation')->where('canUpdateTemplate', true)
            ->has('days.0.exercises.0.sets', 4));
    }

    public function test_explicit_template_update_leaves_other_mesocycles_unchanged(): void
    {
        $mesocycle = $this->createMesocycle();
        $other = Mesocycle::factory()->create(['meso_template_id' => $mesocycle->meso_template_id, 'schema' => $mesocycle->schema]);
        $payload = $this->payload(3);
        $payload['updateTemplate'] = true;
        $payload['name'] = 'Updated prescription';
        $this->patch(route('mesocycles.update', $mesocycle), $payload)->assertSessionHasNoErrors();
        $this->assertSame($mesocycle->fresh()->schema, $mesocycle->mesoTemplate->fresh()->schema);
        $this->assertSame(3, $mesocycle->mesoTemplate->fresh()->planned_stats['sets_per_week']);
        $this->assertSame($other->schema, $other->fresh()->schema);
    }

    public function test_updating_a_template_preserves_legacy_mesocycles_still_using_its_schema(): void
    {
        $mesocycle = $this->createMesocycle();
        $legacy = Mesocycle::factory()->create([
            'meso_template_id' => $mesocycle->meso_template_id,
            'days_per_week' => 1, 'weeks_duration' => 3,
        ]);
        $before = app(MesocyclePlan::class)->resolve($legacy)['schema'];
        $updatedAt = $legacy->getRawOriginal('updated_at');
        $this->patch(route('mesocycles.update', $mesocycle), [...$this->payload(5), 'updateTemplate' => true])
            ->assertSessionHasNoErrors();
        $this->assertEquals($before, $legacy->fresh()->schema);
        $this->assertSame(2, $legacy->fresh()->planned_stats['sets_per_week']);
        $this->assertSame($updatedAt, $legacy->fresh()->getRawOriginal('updated_at'));
        $this->assertSame(5, $mesocycle->mesoTemplate->fresh()->planned_stats['sets_per_week']);
    }

    public function test_a_template_less_mesocycle_can_be_edited_and_saved(): void
    {
        $mesocycle = $this->legacyMesocycle();
        $this->actingAs($mesocycle->user)->get(route('mesocycles.edit', $mesocycle))
            ->assertInertia(fn (Assert $page) => $page->where('canUpdateTemplate', false)->where('days.0.label', 'Week 2'));
        $this->assertNull($mesocycle->fresh()->schema);
        $this->patch(route('mesocycles.update', $mesocycle), $this->payload(3))->assertSessionHasNoErrors();
        $this->assertNotNull($mesocycle->fresh()->schema);
        $this->assertNull($mesocycle->fresh()->meso_template_id);
        $this->assertDatabaseCount('meso_templates', 0);
    }

    public function test_updating_a_missing_or_foreign_template_is_rejected_atomically(): void
    {
        $mesocycle = $this->createMesocycle();
        $template = $mesocycle->mesoTemplate;
        $template->update(['user_id' => User::factory()->create()->id]);
        $before = $mesocycle->getAttributes();
        $payload = [...$this->payload(3), 'updateTemplate' => true];
        $this->patch(route('mesocycles.update', $mesocycle), $payload)->assertSessionHasErrors('updateTemplate');
        $this->assertSame($before, $mesocycle->fresh()->getAttributes());
        $this->assertSame(2, $template->fresh()->planned_stats['sets_per_week']);
        $mesocycle->update(['meso_template_id' => null]);
        $this->patch(route('mesocycles.update', $mesocycle), $payload)->assertSessionHasErrors('updateTemplate');
    }

    public function test_edit_and_update_require_mesocycle_ownership(): void
    {
        $mesocycle = $this->createMesocycle();
        $this->actingAs(User::factory()->create())
            ->get(route('mesocycles.edit', $mesocycle))->assertForbidden();
        $this->patchJson(route('mesocycles.update', $mesocycle), $this->payload())->assertForbidden();
    }

    public function test_template_fallback_uses_mesocycle_metadata_without_persisting_on_read(): void
    {
        $mesocycle = $this->createMesocycle();
        $mesocycle->update(['schema' => null, 'planned_stats' => null, 'name' => 'Legacy name', 'unit' => 'lb']);
        $this->get(route('mesocycles.edit', $mesocycle))->assertInertia(fn (Assert $page) => $page
            ->where('name', 'Legacy name')->where('unit', 'lb')->has('days.0.exercises.0.sets', 2));
        $this->assertNull($mesocycle->fresh()->schema);
    }

    public function test_invalid_or_missing_linked_templates_fall_back_to_workouts(): void
    {
        $mesocycle = $this->legacyMesocycle();
        $template = MesoTemplate::query()->create([
            'name' => 'Old template', 'frequency' => 1, 'user_id' => $mesocycle->user_id,
            'schema' => ['days' => 'invalid'],
        ]);
        $mesocycle->update(['meso_template_id' => $template->id]);
        $plan = app(MesocyclePlan::class);
        $this->assertSame('workouts', $plan->resolve($mesocycle)['source']);
        $mesocycle->update(['meso_template_id' => 999999]);
        $this->assertSame('workouts', $plan->resolve($mesocycle->fresh())['source']);
    }

    public function test_reconstruction_uses_latest_completed_week_and_only_prescribed_fields(): void
    {
        $mesocycle = $this->legacyMesocycle();
        $resolved = app(MesocyclePlan::class)->resolve($mesocycle);
        $this->assertSame(2, $resolved['week']);
        $exercise = $resolved['schema']['days'][0]['exercises'][0];
        $this->assertCount(2, $exercise['sets']);
        $this->assertSame(6, $exercise['sets'][0]['minReps']);
        $this->assertArrayNotHasKey('weight', $exercise['sets'][0]);
        $this->assertArrayNotHasKey('reps', $exercise['sets'][0]);
        $this->assertArrayNotHasKey('finished_at', $exercise['sets'][0]);
        $mesocycle->days()->update(['finished_at' => now()]);
        $this->assertSame(3, app(MesocyclePlan::class)->resolve($mesocycle->fresh())['week']);
    }

    public function test_without_completed_weeks_reconstruction_uses_final_scheduled_week(): void
    {
        $mesocycle = $this->legacyMesocycle();
        $mesocycle->days()->update(['finished_at' => null]);
        $resolved = app(MesocyclePlan::class)->resolve($mesocycle);
        $this->assertSame(3, $resolved['week']);
        $this->assertCount(3, $resolved['schema']['days'][0]['exercises'][0]['sets']);
    }

    public function test_incomplete_weeks_are_not_mistaken_for_fully_completed_weeks(): void
    {
        $mesocycle = $this->legacyMesocycle();
        $mesocycle->update(['days_per_week' => 2]);
        $this->actingAs($mesocycle->user)->get(route('mesocycles.edit', $mesocycle))
            ->assertRedirect(route('mesocycles'))->assertSessionHas('error');
        $this->artisan('mesocycles:backfill-plans', ['--dry-run' => true])
            ->expectsOutputToContain("Skipped mesocycle {$mesocycle->id}")->assertFailed();
        $this->assertNull($mesocycle->fresh()->schema);
    }

    public function test_reconstruction_preserves_day_exercise_and_set_order(): void
    {
        $mesocycle = $this->legacyMesocycle();
        $mesocycle->update(['days_per_week' => 2]);
        $firstDay = $mesocycle->days()->where('week', 3)->first();
        $firstDay->update(['day_order' => 2, 'label' => 'Second day']);
        $otherDay = MesoDay::factory()->for($mesocycle)->create(['week' => 3, 'day_order' => 1, 'label' => 'First day']);
        $exercise = Exercise::query()->firstOrFail();
        foreach ([2, 1] as $position) {
            $exercise = Exercise::query()->orderBy('id')->skip($position)->firstOrFail();
            $dayExercise = DayExercise::factory()->for($otherDay, 'day')->for($exercise)->create([
                'position' => $position, 'one_rep_max' => $position * 50,
            ]);
            ExerciseSet::factory()->for($dayExercise)->create(['min_reps' => $position, 'max_reps' => 10]);
        }
        $resolved = app(MesocyclePlan::class)->resolve($mesocycle);
        $this->assertSame(['First day', 'Second day'], array_column($resolved['schema']['days'], 'label'));
        $this->assertEquals([50, 100], array_column($resolved['schema']['days'][0]['exercises'], 'oneRepMax'));
    }

    public function test_dry_run_and_user_filter_then_backfill_preserve_workouts_templates_and_timestamps(): void
    {
        $mesocycle = $this->legacyMesocycle();
        $other = $this->legacyMesocycle();
        $dayBefore = $mesocycle->days()->first()->getAttributes();
        $setsBefore = ExerciseSet::query()->get()->map->getAttributes()->all();
        $updatedAt = $mesocycle->getRawOriginal('updated_at');
        $this->artisan('mesocycles:backfill-plans', ['--dry-run' => true, '--user' => $mesocycle->user_id])
            ->expectsOutputToContain("Would populate mesocycle {$mesocycle->id} from workouts week 2")
            ->assertSuccessful();
        $this->assertNull($mesocycle->fresh()->schema);
        $this->artisan('mesocycles:backfill-plans', ['--user' => $mesocycle->user_id])->assertSuccessful();
        $this->assertSame('Week 2', $mesocycle->fresh()->schema['days'][0]['label']);
        $this->assertSame(2, $mesocycle->fresh()->planned_stats['sets_per_week']);
        $this->assertNull($other->fresh()->schema);
        $this->assertSame($updatedAt, $mesocycle->fresh()->getRawOriginal('updated_at'));
        $this->assertSame($dayBefore, $mesocycle->days()->first()->getAttributes());
        $this->assertSame($setsBefore, ExerciseSet::query()->get()->map->getAttributes()->all());
        $this->assertDatabaseCount('meso_templates', 0);
        $before = $mesocycle->fresh()->getAttributes();
        $this->artisan('mesocycles:backfill-plans', ['--user' => $mesocycle->user_id])
            ->expectsOutputToContain('0 mesocycles populated')->assertSuccessful();
        $this->assertSame($before, $mesocycle->fresh()->getAttributes());
    }

    public function test_backfill_copies_template_and_fills_missing_stats_without_overwriting_existing_schemas(): void
    {
        $mesocycle = $this->createMesocycle();
        $templateBefore = $mesocycle->mesoTemplate->getAttributes();
        $mesocycle->update(['schema' => null, 'planned_stats' => null]);
        $this->artisan('mesocycles:backfill-plans')->assertSuccessful();
        $this->assertSame($mesocycle->mesoTemplate->schema, $mesocycle->fresh()->schema);
        $this->assertSame($templateBefore, $mesocycle->mesoTemplate->fresh()->getAttributes());
        $schema = $this->payload(5);
        $mesocycle->refresh()->update(['schema' => $schema, 'planned_stats' => null]);
        $schema = $mesocycle->fresh()->schema;
        $this->artisan('mesocycles:backfill-plans')->assertSuccessful();
        $this->assertSame($schema, $mesocycle->fresh()->schema);
        $this->assertSame(5, $mesocycle->fresh()->planned_stats['sets_per_week']);
    }

    public function test_backfill_reports_unusable_records_and_continues_with_other_users(): void
    {
        $empty = Mesocycle::factory()->create();
        $valid = $this->legacyMesocycle();
        $this->artisan('mesocycles:backfill-plans')
            ->expectsOutputToContain("Skipped mesocycle {$empty->id}")->assertFailed();
        $this->assertNull($empty->fresh()->schema);
        $this->assertNotNull($valid->fresh()->schema);
        $this->artisan('mesocycles:backfill-plans', ['--user' => 'invalid'])->assertExitCode(2);
    }

    public function test_weekly_stats_group_by_actual_primary_muscle_and_do_not_follow_logged_sets(): void
    {
        $mesocycle = $this->createMesocycle();
        $payload = $this->payload(2);
        $first = Exercise::query()->firstOrFail();
        $second = Exercise::query()->where('muscle_group_id', '!=', $first->muscle_group_id)->firstOrFail();
        $payload['days'][] = $payload['days'][0];
        $payload['days'][1]['exercises'][] = [
            'exerciseId' => $second->id,
            'muscleGroup' => $first->muscle_group_id,
            'sets' => [], 'oneRepMax' => null,
        ];
        $this->patch(route('mesocycles.update', $mesocycle), $payload)->assertSessionHasNoErrors();
        $stats = $mesocycle->fresh()->planned_stats;
        $this->assertSame(2, $stats['muscle_groups_count']);
        $this->assertSame(5, $stats['sets_per_week']);
        $this->assertSame([4, 1], array_column($stats['muscle_groups'], 'sets'));
        $dayExercise = $mesocycle->days()->first()->dayExercises()->first();
        ExerciseSet::factory()->for($dayExercise)->create();
        $this->assertSame($stats, $mesocycle->fresh()->planned_stats);
        $this->get(route('mesocycles'))->assertInertia(fn (Assert $page) => $page
            ->where('mesocycles.0.planned_stats.sets_per_week', 5));
    }
}
