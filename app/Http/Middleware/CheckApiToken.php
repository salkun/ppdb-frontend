<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('api_token') || empty($request->session()->get('api_token'))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Token tidak ditemukan di sesi.'
                ], 401);
            }

            return redirect()->route('login')->with('warning', 'Silakan masuk terlebih dahulu untuk mengakses portal PPDB.');
        }

        return $next($request);
    }
}
