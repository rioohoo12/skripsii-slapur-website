<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotAnswer extends Model
{
    protected $fillable = [
        'topic',
        'step_key',
        'trigger_keywords',
        'response',
        'next_step_key',
        'order',
        'is_initial',
    ];

    protected $casts = [
        'trigger_keywords' => 'array',
        'is_initial' => 'boolean',
    ];
}
