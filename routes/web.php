<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FizzBuzzController;
use App\Http\Middleware\EnsureApiKey;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/fizzbuzz', [FizzBuzzController::class, 'fizzbuzz'])
    ->middleware(EnsureApiKey::class);
