<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\DemoService;

class DemoController extends Controller
{
    public function start(DemoService $demo)
    {
        // Throttle to avoid abuse (per IP)
        $key = 'demo:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            abort(429, 'Too many demo requests. Try again later.');
        }
        RateLimiter::hit($key, 300); // 5 minutes

        // Reuse existing demo user for this session if present
        if ($id = session('demo_user_id')) {
            if ($user = User::find($id)) {
                Auth::login($user);
                return redirect()->route('mesocycles');
            }
        }

        $user = User::create([
            'name'              => 'Demo User',
            'email'             => 'demo+' . Str::uuid() . '@example.test',
            'password'          => bcrypt(Str::random(32)), // Will never be used
        ]);

        $user->forceFill([
            'created_at'        => now()->subWeeks(6),
            'email_verified_at' => now(),
        ])->save();

        $demo->seedFor($user);

        session(['demo_user_id' => $user->id]); // remember across refreshes

        Auth::login($user);

        return redirect()->route('mesocycles');
    }
}
