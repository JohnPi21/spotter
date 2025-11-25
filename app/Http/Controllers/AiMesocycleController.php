<?php

namespace App\Http\Controllers;

use App\Ai\Agents\MesocycleAgent;
use Illuminate\Http\Request;

class AiMesocycleController extends Controller
{
    // Request should be AiMesocycleRequest
    public function generate(Request $request, MesocycleAgent $agent)
    {
        // transform to dto

        // pass to agent
        $schema = $agent->generate();


        // send user to preview page
        // return Inertia::render("Mesocycle/Edit");
    }
}
