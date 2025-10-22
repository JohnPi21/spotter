<?php

namespace App\Http\Controllers;

use App\Models\Mesocycle;
use App\Models\User;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        if (! app()->environment('local')) {
            abort(404);
        }

        dd(Mesocycle::first());
    }
}
