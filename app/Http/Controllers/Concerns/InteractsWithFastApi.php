<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

trait InteractsWithFastApi
{
    /**
     * Get the backend API base URL without trailing slash.
     */
    protected function backendUrl(): string
    {
        return rtrim(config('ppdb.api_url', 'http://127.0.0.1:8000'), '/');
    }

    /**
     * Get active API JWT token from session.
     */
    protected function apiToken(): ?string
    {
        return session('api_token');
    }

    /**
     * Get active Admin API JWT token from session.
     */
    protected function adminApiToken(): ?string
    {
        return session('admin_api_token');
    }

    /**
     * Create a standard Http client instance.
     */
    protected function httpClient()
    {
        return Http::timeout(config('ppdb.timeout', 15))
            ->acceptJson();
    }

    /**
     * Create an authenticated Http client instance with the session token.
     */
    protected function httpWithToken()
    {
        return $this->httpClient()
            ->withToken($this->apiToken());
    }

    /**
     * Create an authenticated Http client instance with the admin session token.
     */
    protected function httpWithAdminToken()
    {
        return $this->httpClient()
            ->withToken($this->adminApiToken());
    }

    /**
     * Extract human-readable error messages from FastAPI responses.
     */
    protected function extractErrorMessage(Response $response, string $fallback = 'Terjadi kesalahan pada server.'): string
    {
        $json = $response->json();

        if (is_array($json)) {
            if (isset($json['detail'])) {
                if (is_string($json['detail'])) {
                    return $json['detail'];
                }

                if (is_array($json['detail'])) {
                    // FastAPI validation errors structure
                    $messages = [];
                    foreach ($json['detail'] as $err) {
                        if (is_array($err)) {
                            $field = isset($err['loc']) ? implode(' -> ', array_slice($err['loc'], 1)) : 'Field';
                            $msg = $err['msg'] ?? 'Tidak valid';
                            $messages[] = "{$field}: {$msg}";
                        } elseif (is_string($err)) {
                            $messages[] = $err;
                        }
                    }
                    if (!empty($messages)) {
                        return implode(', ', $messages);
                    }
                }
            }

            if (isset($json['message']) && is_string($json['message'])) {
                return $json['message'];
            }
        }

        return $fallback . ' (Kode Status: ' . $response->status() . ')';
    }
}
