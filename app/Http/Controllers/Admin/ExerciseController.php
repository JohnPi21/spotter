<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Exercise\ListExercises;
use App\Enums\EquipmentsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExerciseIndexRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ExerciseIndexRequest $request, ListExercises $listExercises): Response
    {
        $exercises = $listExercises->execute($request->validated());

        return Inertia::render('Admin/Exercises/Index', [
            'exercises' => $exercises,
            'exerciseTypes' => EquipmentsEnum::options(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
