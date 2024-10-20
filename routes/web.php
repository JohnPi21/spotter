<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia::render('hello');
});

Route::get('/mesocycles', function() {
    return Inertia::render('mesocycles');
});
