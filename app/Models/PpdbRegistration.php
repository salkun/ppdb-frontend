<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbRegistration extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ppdb_registrations';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'account_id',
        'payment_status',
        'payment_method',
        'payment_amount',
        'payment_proof_path',
        'payment_verified_at',
        'payment_verified_by',
        'registration_status',
        'form_data',
        'student_id',
        'remote_id',
        'sync_status',
        'sync_message',
        'last_synced_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'payment_amount' => 'decimal:2',
        'payment_verified_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(PpdbAccount::class, 'account_id');
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class, 'syncable_id')->where('syncable_type', 'registration');
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

    /**
     * Get candidate major name from form_data.
     */
    public function getMajorAttribute(): ?string
    {
        return $this->form_data['major'] ?? null;
    }
}
