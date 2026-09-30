<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MaintenanceCountdown
{
    /**
     * Tanggal & jam pembukaan kembali (WIB).
     * Ubah value ini untuk mengatur kapan web dibuka.
     */
    protected string $openAt = '2026-10-05 05:00:00';
    protected string $timezone = 'Asia/Jakarta';

    /**
     * Route names yang tetap boleh diakses meskipun dalam masa countdown.
     */
    protected array $exceptRoutes = [
        'admin.*',
        'api.*',
        'uploads.serve',
    ];

    /**
     * URI prefixes yang tetap boleh diakses.
     */
    protected array $exceptPrefixes = [
        'admin',
        'api',
        'generate-key',
        'migrate',
        'migrate-seed',
        'storage-link',
        'link-storage',
        'linkstorage',
        'storage/link',
        'clear-cache',
        'sync-uploads',
        'uploads',
        'countdown',
    ];

    public function handle(Request $request, Closure $next)
    {
        $now = Carbon::now($this->timezone);
        $openDate = Carbon::parse($this->openAt, $this->timezone);

        if ($now->gte($openDate)) {
            return $next($request);
        }

        if ($this->isExcepted($request)) {
            return $next($request);
        }

        return response()->view('countdown', [
            'openAt' => $openDate->toIso8601String(),
            'openAtFormatted' => $openDate->translatedFormat('l, d F Y — H:i') . ' WIB',
        ]);
    }

    protected function isExcepted(Request $request): bool
    {
        $routeName = $request->route()?->getName() ?? '';
        foreach ($this->exceptRoutes as $pattern) {
            if (str_ends_with($pattern, '*')) {
                $prefix = rtrim($pattern, '.*');
                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }
            } elseif ($routeName === $pattern) {
                return true;
            }
        }

        $uri = trim($request->path(), '/');
        foreach ($this->exceptPrefixes as $prefix) {
            if ($uri === $prefix || str_starts_with($uri, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }
}
