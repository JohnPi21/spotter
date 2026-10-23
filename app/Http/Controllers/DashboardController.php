<?php

namespace App\Http\Controllers;

use App\Services\DashboardTraining;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardTraining $training): Response
    {
        return Inertia::render('Dashboard', [
            'training' => $training->forUser($request->user()->id),
        ]);
    }
}
