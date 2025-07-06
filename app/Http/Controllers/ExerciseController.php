<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(): JsonResponse
    {
        // $exercisesByMuscles = MuscleGroup::with('exercises')->get();

        // $exercisesByMuscles->each(function ($muscleGroup) {
        //     // Basically does this: $muscleGroup = $muscleGroup->exercises->setKey('id');
        //     $muscleGroup->setRelation('exercises', $muscleGroup->exercises->keyBy('id'));
        // });

        return response()->json([
            'exercises' => Exercise::select('id', 'name', 'muscle_group_id', 'youtube_id', 'exercise_type')->get()->keyBy('id'),
            'muscleGroups' => MuscleGroup::all()->keyBy('id'),
        ]);
    }
}
