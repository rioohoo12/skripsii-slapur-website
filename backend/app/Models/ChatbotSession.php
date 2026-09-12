<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotSession extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'current_step_key',
        'registration_data',
        'last_activity_at',
    ];

    protected $casts = [
        'registration_data' => 'array',
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
