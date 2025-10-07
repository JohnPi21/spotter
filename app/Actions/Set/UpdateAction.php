<?php

namespace App\Actions\Set;

use App\Models\DayExercise;
use App\Data\Set\UpdateData as SetUpdateData;
use App\Http\Requests\UpdateSetRequest;
use App\Models\ExerciseSet;

class UpdateAction
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public static function execute(UpdateSetRequest $request, ExerciseSet $set)
    {
        $setDTO = SetUpdateData::from($request->validated());

        $set->update($setDTO->toArray());
    }
}
