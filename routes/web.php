<?php

use App\Livewire\WeeklyPlanner;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

require __DIR__.'/settings.php';

Route::get('planner', WeeklyPlanner::class)
    ->middleware(['auth', 'verified'])
    ->name('planner');
