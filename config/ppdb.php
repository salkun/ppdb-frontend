<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backend API URL
    |--------------------------------------------------------------------------
    |
    | Base URL of the FastAPI backend for PPDB and SIAKAD services.
    |
    */
    'api_url' => env('API_BACKEND_URL', 'http://127.0.0.1:8000'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Request Timeout
    |--------------------------------------------------------------------------
    |
    | The default timeout in seconds for requests sent to the backend.
    |
    */
    'timeout' => env('API_BACKEND_TIMEOUT', 15),
];
