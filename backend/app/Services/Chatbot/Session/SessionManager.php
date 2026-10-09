<?php

namespace App\Services\Chatbot\Session;

use App\Models\ChatSession;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SessionManager
{
    /**
     * Get active session or create a new one.
     */
    public function getOrCreateSession(?string $sessionId, ?int $userId = null): ChatSession
    {
        $timeoutMinutes = config('chatbot.session_timeout_minutes', 30);
        $session = null;

        if ($sessionId) {
            $session = ChatSession::where('session_id', $sessionId)->first();
        }

        // If session found but expired or inactive, we start fresh (or return it and let caller decide)
        if ($session && $session->last_activity_at) {
            $lastActivity = Carbon::parse($session->last_activity_at);
            if ($lastActivity->diffInMinutes(now()) > $timeoutMinutes) {
                $session->update(['status' => 'expired']);
                $session = null; // force create new
                $sessionId = null; // force generate new UUID to prevent UNIQUE constraint violation
            }
        }

        if (!$session) {
            $session = ChatSession::create([
                'session_id' => $sessionId ?: Str::uuid()->toString(),
                'user_id' => $userId,
                'guest_token' => $userId ? null : Str::random(40),
                'current_flow' => 'default',
                'current_step' => 'init',
                'slots' => json_encode([]),
                'status' => 'active',
                'last_activity_at' => now(),
            ]);
        } else {
            // Update last activity
            $session->update(['last_activity_at' => now()]);
            // Link user id if they just logged in
            if ($userId && !$session->user_id) {
                $session->update(['user_id' => $userId]);
            }
        }

        return $session;
    }

    /**
     * Update session state.
     */
    public function updateState(ChatSession $session, string $flow, string $step, array $slots): void
    {
        $session->update([
            'current_flow' => $flow,
            'current_step' => $step,
            'slots' => json_encode($slots),
            'last_activity_at' => now(),
        ]);
    }
    
    /**
     * Get recent context (short term memory).
     */
    public function getRecentContext(ChatSession $session, int $limit = 5): array
    {
        return $session->messages()
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->reverse()
            ->toArray();
    }
}
