<?php

use Illuminate\Support\Facades\Route;

// Serve Vue SPA for all non-API web routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
