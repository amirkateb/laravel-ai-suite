<?php

namespace AmirKateb\AiSuite\Models;

use Illuminate\Database\Eloquent\Model;

class AiLog extends Model
{
    protected $table = 'ai_suite_logs';

    protected $fillable = [
        'provider',
        'driver',
        'model',
        'operation',
        'status',
        'error_code',
        'error_message',
        'user_id',
        'request_id',
        'conversation_id',
        'ip',
        'duration_ms',
        'input_tokens',
        'output_tokens',
        'cost',
        'currency',
        'request_payload',
        'response_payload',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'duration_ms' => 'integer',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost' => 'decimal:8',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}