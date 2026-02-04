<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FizzBuzzController extends Controller
{
    public function fizzbuzz() {
        return response('FizzBuzz', 200)
            ->header('Content-Type', 'text/plain');
    }
}
