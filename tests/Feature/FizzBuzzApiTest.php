<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FizzBuzzApiTest extends TestCase
{
    // tests/Feature/FizzBuzzApiTest.php
    public function test_fizzbuzz_requires_api_key()
    {
        $response = $this->getJson('/fizzbuzz');
        $response->assertStatus(401);
    }

    public function test_fizzbuzz_rejects_invalid_api_key()
    {
        $response = $this->getJson('/fizzbuzz', [
            'API_KEY' => 'wrong-key'
        ]);
        $response->assertStatus(403);
    }

    public function test_fizzbuzz_returns_correct_response()
    {
        $response = $this->getJson('/fizzbuzz', [
            'API_KEY' => env('APP_API_KEY')
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure(['success', 'data', 'timestamp', 'status']);
    }
}
