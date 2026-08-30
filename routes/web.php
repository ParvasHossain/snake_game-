<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScoreController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/save-score', [ScoreController::class, 'store'])
    ->name('scores.store');

Route::get('/scores', [ScoreController::class, 'index'])
    ->name('scores.index');