<?php

use FilaMan\Pages\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pages Plugin Routes
|--------------------------------------------------------------------------
|
| These routes handle the public-facing pages functionality.
| All routes are unauthenticated and prefixed with 'pages'.
|
*/

// Routes are prefixed with 'pages' to prevent conflicts and keep them isolated.
Route::prefix('pages')->name('filaman.pages.')->group(function () {
    // List all available pages
    Route::get('/', [PageController::class, 'index'])->name('index');

    // Show specific page by slug, with 'home' as default
    Route::get('{slug}', [PageController::class, 'show'])->name('show');
});

// Optional: Create a root redirect to the home page
Route::get('/pages', function () {
    return redirect()->route('filaman.pages.show', ['slug' => 'home']);
});
