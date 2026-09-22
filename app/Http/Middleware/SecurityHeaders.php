<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach recommended security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah browser melakukan MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Mencegah Clickjacking dengan membatasi iframe hanya untuk origin yang sama
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Mengaktifkan filter XSS di browser terdahulu
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Mengontrol informasi referrer saat navigasi keluar
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Membatasi akses ke fitur sensitif browser (kamera, mikrofon, geolokasi)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS jika koneksi HTTPS (aktif otomatis pada server produksi dengan HTTPS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
