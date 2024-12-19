<?php

namespace App\Http\Controllers;

use App\Models\Mesocycle;
use App\Models\User;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class MesocycleController extends Controller
{
    public function index(): \Inertia\Response 
    {
        return Inertia::render('mesocycles/index');
    }

    public function show(int $id): \Inertia\Response
    {
        return Inertia::render('mesocycles/show', ['id' => $id]);
    }

    public function create(): \Inertia\Response
    {
        $muscleGroups = MuscleGroup::pluck('name', 'id')->toArray();
        $exercises = Exercise::all();

        return Inertia::render('mesocycles/create', [
            'muscleGroups' => $muscleGroups,
            'exercises' => $exercises
        ]);
    }
}
