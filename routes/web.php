<?php

use App\Livewire\WeeklyPlanner;
use App\Livewire\Components\PeriodMetrics;
use App\Livewire\Components\Pizarra;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

require __DIR__.'/settings.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('planner/{period?}', WeeklyPlanner::class)->name('planner');
    Route::get('metrics/{period?}', PeriodMetrics::class)->name('metrics');
    Route::get('pizarra', Pizarra::class)->name('pizarra');
});