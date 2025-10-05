<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReadCsvController;

ini_set('max_execution_time', 1200);

Route::get('/', function () {

     if (auth()->check()) {
        return redirect()->route('projects.index');
    }
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

Route::get('/projects', [App\Http\Controllers\ProjectController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('projects.index');
Route::get('/projects/create', [App\Http\Controllers\ProjectController::class, 'create'])
    ->middleware(['auth', 'verified'])->name('projects.create');

Route::post('/projects', [App\Http\Controllers\ProjectController::class, 'store'])
    ->middleware(['auth', 'verified'])->name('projects.store');

Route::get('/projects/{project}', [App\Http\Controllers\ProjectController::class, 'show'])
    ->middleware(['auth', 'verified'])->name('projects.show');

Route::post('/projects/{project}/upload', [App\Http\Controllers\ProjectController::class, 'upload'])
    ->middleware(['auth', 'verified'])->name('projects.upload');

Route::delete('/projectdata/{projectData}', [App\Http\Controllers\ProjectDataController::class, 'destroy'])
    ->middleware(['auth', 'verified'])->name('projectdata.destroy');  
    
Route::get('/projects/{project}/reports', [App\Http\Controllers\ReportController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('projects.reports.index');
Route::post('/projects/{project}/greports', [App\Http\Controllers\ReportController::class, 'create'])
    ->middleware(['auth', 'verified'])->name('projects.reports.create');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
