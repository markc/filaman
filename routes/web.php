<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Check if any plugins exist in the plugins directory
    $pluginsPath = base_path('plugins');
    $hasPlugins = File::exists($pluginsPath) && count(File::directories($pluginsPath)) > 0;

    if ($hasPlugins) {
        // If plugins exist, redirect to admin panel
        return redirect('/admin');
    } else {
        // No plugins, show welcome page
        return view('welcome-simple');
    }
});
