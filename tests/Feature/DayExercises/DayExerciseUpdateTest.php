<?php

namespace Tests\Feature\DayExercises;

use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\MesoDay;
use App\Models\Mesocycle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayExerciseUpdateTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_can_reorder_their_day_exercises()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day  = $meso->days()->first();

        // Ensure we have at least 3 DayExercises on the day with distinct exercises
        $existingIds   = $day->dayExercises()->pluck('exercise_id');
        $moreExercises = Exercise::whereNotIn('id', $existingIds)->limit(2)->pluck('id');
        foreach ($moreExercises as $exId) {
            DayExercise::create([
                'meso_day_id' => $day->id,
                'exercise_id' => $exId,
                'position'    => (DayExercise::where('meso_day_id', $day->id)->max('position') ?? 0) + 1,
            ]);
        }

        $ids = $day->refresh()->dayExercises()->pluck('id', 'position')->toArray();
        $reversed = array_reverse($ids);

        $this->actingAs($user)
            ->patch(route('dayExercises.reorder', $day), ['order' => $reversed])
            ->assertRedirect(route('days.show', [$meso, $day]))
            ->assertSessionHasNoErrors();

        // Assert positions match new order (0..n following $reversed)
        // foreach ($reversed as $pos => $id) {
        //     $this->assertDatabaseHas('day_exercises', ['id' => $id, 'position' => $pos]);
        // }
    }


    public function test_user_cannot_reorder_someone_elses_day()
    {
        [$owner, $other] = User::factory()->count(2)->create();
        $meso = Mesocycle::factory()->for($owner)->withFullStructure()->create();
        $day  = $meso->days()->first();

        // Make sure there are at least 2
        if ($day->dayExercises()->count() < 2) {
            $exId = Exercise::whereNotIn('id', $day->dayExercises()->pluck('exercise_id'))->value('id');
            DayExercise::create([
                'meso_day_id' => $day->id,
                'exercise_id' => $exId,
                'position'    => (DayExercise::where('meso_day_id', $day->id)->max('position') ?? 0) + 1,
            ]);
        }

        $ids = $day->refresh()->dayExercises()->orderBy('position')->pluck('id', 'position')->toArray();
        $reversed = array_reverse($ids);

        $this->from(route('days.show', [$meso, $day]))
            ->actingAs($other)
            ->patch(route('dayExercises.reorder', $day), ['order' => $reversed])
            ->assertRedirectBackWithErrors();

        // foreach ($ids as $pos => $id) {
        //     $this->assertDatabaseHas('day_exercises', ['id' => $id, 'position' => $pos]);
        // }
    }


    public function test_reorder_fails_validation_on_duplicate_ids()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day  = $meso->days()->first();

        // Ensure at least 2 entries
        if ($day->dayExercises()->count() < 2) {
            $exId = Exercise::whereNotIn('id', $day->dayExercises()->pluck('exercise_id'))->value('id');
            DayExercise::create([
                'meso_day_id' => $day->id,
                'exercise_id' => $exId,
                'position'    => (DayExercise::where('meso_day_id', $day->id)->max('position') ?? -1) + 1,
            ]);
        }

        $ids = $day->refresh()->dayExercises()->orderBy('position')->pluck('id')->toArray();
        $badOrder = [$ids[0], $ids[0]];

        $this->from(route('days.show', [$meso, $day]))
            ->actingAs($user)
            ->patch(route('dayExercises.reorder', $day), ['order' => $badOrder])
            ->assertRedirectBack();
        // ->assertSessionHasErrors();
    }

    public function test_user_can_save_note_for_day_exercise()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day = $meso->days()->first();
        $dayExercise = $day->dayExercises()->first();

        $payload = [
            'day_exercise_id' => $dayExercise->id,
            'note' => 'Focus on form and tempo.',
        ];

        $this->actingAs($user)
            ->put(route('dayExercises.saveNote', $day), $payload)
            ->assertRedirect(route('days.show', [$meso, $day]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('day_exercises', [
            'id' => $dayExercise->id,
            'note' => $payload['note'],
        ]);
    }

    public function test_user_can_delete_note_for_day_exercise()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day = $meso->days()->first();
        $dayExercise = $day->dayExercises()->first();

        $dayExercise->update(['note' => 'Delete me.']);

        $this->actingAs($user)
            ->delete(route('dayExercises.deleteNote', $day), [
                'day_exercise_id' => $dayExercise->id,
            ])
            ->assertRedirect(route('days.show', [$meso, $day]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('day_exercises', [
            'id' => $dayExercise->id,
            'note' => null,
        ]);
    }
}
