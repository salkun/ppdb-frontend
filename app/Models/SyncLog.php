<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory;

    protected $table = 'sync_logs';

    protected $fillable = [
        'syncable_type',
        'syncable_id',
        'action',
        'api_endpoint',
        'http_method',
        'request_payload',
        'response_status',
        'response_body',
        'status',
        'error_message',
        'attempt',
        'executed_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_body' => 'array',
        'executed_at' => 'datetime',
    ];
}
