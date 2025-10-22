<?php

namespace App\Actions\DayExercise;

use App\Models\MesoDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReorderDayExercises
{
    public function __construct() {}

    public function execute(MesoDay $day, $ids): void
    {
        $orderIds   = collect($ids);
        $sortedIds  = collect($ids)->sort()->values()->all();

        DB::transaction(function () use ($orderIds, $day, $sortedIds) {
            $dayExercises = $day->dayExercises()->lockForUpdate()->orderBy('id')->pluck('id');

            if ($dayExercises->all() !== $sortedIds) {
                throw ValidationException::withMessages(['orderIds' => __('Not all exercises belong to the current day.')]);
            }

            $placeholders = implode(',', array_fill(0, $orderIds->count(), '?'));

            DB::update("
                UPDATE day_exercises
                SET position = position + 100
                where meso_day_id = ?
            ", [$day->id]);

            DB::update("
                UPDATE day_exercises
                SET position = FIELD(id, $placeholders)
                WHERE meso_day_id = ?
            ", [...$orderIds->all(), $day->id]);
        });
    }
}
