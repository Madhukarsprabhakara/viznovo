<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReadCsvController;

ini_set('max_execution_time', 1200);

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('danad', function () {
    return Inertia::render('Danad');
})->middleware(['auth', 'verified'])->name('danad');

Route::get('readcsv', [ReadCsvController::class, 'readCSV'])
    ->middleware(['auth', 'verified'])
    ->name('readcsv');

Route::get('llamacsv', [ReadCsvController::class, 'getllama'])
    ->middleware(['auth', 'verified'])
    ->name('llamacsv');

    
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
