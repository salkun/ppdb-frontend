<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminApiToken
{
    /**
     * Handle an incoming request for Admin routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('admin_api_token') || empty($request->session()->get('admin_api_token'))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Token admin tidak ditemukan di sesi.'
                ], 401);
            }

            return redirect()->route('admin.login')->with('warning', 'Silakan masuk dengan akun Administrator terlebih dahulu.');
        }

        return $next($request);
    }
}
