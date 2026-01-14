<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\WeeklyPlanner;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';

Route::get('planner', WeeklyPlanner::class)
    ->middleware(['auth', 'verified'])
    ->name('planner');