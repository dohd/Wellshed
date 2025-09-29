<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $allowedHeaders = 'Content-Type, Authorization, X-Requested-With, X-Onboarding-Token';

        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', '*'); // or your frontend domain
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', $allowedHeaders);

        // Handle Preflight OPTIONS Requests
        if ($request->getMethod() === "OPTIONS") {
            return response()->json('OK', 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => $allowedHeaders,
            ]);
        }

        return $response;
    }

}