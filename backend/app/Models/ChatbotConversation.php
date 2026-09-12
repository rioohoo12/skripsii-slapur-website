<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    protected $fillable = [
        'session_id',
        'role',
        'message',
        'answer_id',
    ];
}
