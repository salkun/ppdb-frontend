<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbAccount extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ppdb_accounts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nik',
        'full_name',
        'email',
        'password',
        'phone',
        'is_active',
        'remote_id',
        'sync_status',
        'sync_message',
        'last_synced_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->hasOne(PpdbRegistration::class, 'account_id');
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class, 'syncable_id')->where('syncable_type', 'account');
    }

    public function needsSync(): bool
    {
        return in_array($this->sync_status, ['pending', 'failed']);
    }

    public function markSynced(?string $remoteId = null, ?string $message = null): void
    {
        $data = [
            'sync_status' => 'synced',
            'sync_message' => $message,
            'last_synced_at' => now(),
        ];
        if ($remoteId) {
            $data['remote_id'] = $remoteId;
        }
        $this->update($data);
    }

    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'sync_status' => 'failed',
            'sync_message' => $errorMessage,
        ]);
    }
}
