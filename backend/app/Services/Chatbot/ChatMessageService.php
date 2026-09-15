<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotConversation;
use Illuminate\Support\Collection;

class ChatMessageService
{
    /**
     * Simpan pesan dari pengguna.
     */
    public function recordUserMessage(string $sessionId, string $message): ChatbotConversation
    {
        return ChatbotConversation::create([
            'session_id' => $sessionId,
            'role' => 'user',
            'message' => $message,
        ]);
    }

    /**
     * Simpan pesan balasan dari assistant.
     */
    public function recordAssistantResponse(string $sessionId, string $reply, ?int $answerId = null): ChatbotConversation
    {
        return ChatbotConversation::create([
            'session_id' => $sessionId,
            'role' => 'assistant',
            'message' => $reply,
            'answer_id' => $answerId,
        ]);
    }

    /**
     * Ambil riwayat percakapan untuk session ID tertentu.
     */
    public function getHistory(string $sessionId): Collection
    {
        return ChatbotConversation::where('session_id', $sessionId)
            ->orderBy('id')
            ->get(['role', 'message', 'created_at'])
            ->map(fn ($m) => [
                'role' => $m->role,
                'message' => $m->message,
                'created_at' => $m->created_at->toIso8601String(),
            ]);
    }

    /**
     * Hapus riwayat chat sesi tertentu.
     */
    public function clearHistory(string $sessionId): int
    {
        return ChatbotConversation::where('session_id', $sessionId)->delete();
    }
}
