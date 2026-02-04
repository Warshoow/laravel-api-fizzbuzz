<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FizzBuzzController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/fizzbuzz', [FizzBuzzController::class, 'fizzbuzz']);
