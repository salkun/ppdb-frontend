<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'account_id',
        'status',
        'started_at',
        'ends_at',
        'completed_at',
        'violations',
        'last_violation_at',
        'violation_log',
        'question_order',
        'option_orders',
        'answers',
        'score',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_violation_at' => 'datetime',
        'violation_log' => 'array',
        'question_order' => 'array',
        'option_orders' => 'array',
        'answers' => 'array',
        'score' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(PpdbAccount::class, 'account_id');
    }
}
