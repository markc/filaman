<?php

use FilaMan\Pages\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/pages')->group(function () {
    Route::get('/', [ApiController::class, 'index']);
    Route::get('/categories', [ApiController::class, 'categories']);
    Route::get('/search', [ApiController::class, 'search']);
    Route::get('/{slug}', [ApiController::class, 'show']);
});
