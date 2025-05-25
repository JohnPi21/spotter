<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MuscleGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(): JsonResponse
    {
        $exercisesByMuscles = MuscleGroup::with('exercises')->get();

        return response()->json($exercisesByMuscles);
    }
}