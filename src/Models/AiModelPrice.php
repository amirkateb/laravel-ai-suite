<?php

namespace AmirKateb\AiSuite\Models;

use Illuminate\Database\Eloquent\Model;

class AiModelPrice extends Model
{
    protected $table = 'ai_suite_model_prices';

    protected $fillable = [
        'provider',
        'model',
        'input_per_1m',
        'output_per_1m',
        'cached_input_per_1m',
        'unit',
        'currency',
        'source'
    ];

    protected $casts = [
        'input_per_1m' => 'decimal:8',
        'output_per_1m' => 'decimal:8',
        'cached_input_per_1m' => 'decimal:8',
        'unit' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}