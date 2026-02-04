<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FizzBuzzController extends Controller
{
    public function fizzbuzz() {
        return response()->json([
            'data' => 'FizzBuzz',
            'status' => 200,
            'success' => true,
            'timestamp' => now()->toISOString()
        ])
            ->header('Content-Type', 'application/json');
    }
}
