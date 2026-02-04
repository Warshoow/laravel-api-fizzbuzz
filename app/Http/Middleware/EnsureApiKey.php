<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('API_KEY');
        $expectedApiKey = env('APP_API_KEY');


        if (!$request->hasHeader('API_KEY')) {
            return response()->json([
                'success' => false,
                'error' => 'Authentication Required',
                'message' => 'API key is required. Please provide a valid API key in the API_KEY header.',
                'status' => 401,
                'timestamp' => now()->toISOString(),
            ], 401);
        }

        
        if ($apiKey !== $expectedApiKey) {
            return response()->json([
                'success' => false,
                'error' => 'Authentication Failed',
                'message' => 'Invalid API key provided. Please check your API key and try again.',
                'status' => 403,
                'timestamp' => now()->toISOString(),
            ], 403);
        }

        return $next($request);
    }
}
