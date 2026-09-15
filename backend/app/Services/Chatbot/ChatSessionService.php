<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotSession;
use App\Models\User;
use Illuminate\Support\Str;

class ChatSessionService
{
    /**
     * Dapatkan atau buat sesi percakapan berdasarkan session ID dan user.
     */
    public function getOrCreateSession(?string $sessionId, ?User $user = null): ChatbotSession
    {
        $resolvedSessionId = $sessionId ?: Str::uuid()->toString();

        $session = ChatbotSession::firstOrCreate(
            ['session_id' => $resolvedSessionId],
            [
                'user_id' => $user?->id,
                'current_step_key' => null,
                'registration_data' => [],
                'last_activity_at' => now(),
            ]
        );

        if ($user && $session->user_id !== $user->id) {
            $session->update(['user_id' => $user->id]);
        }

        $session->update(['last_activity_at' => now()]);

        return $session;
    }

    /**
     * Perbarui langkah/step percakapan saat ini.
     */
    public function updateStep(ChatbotSession $session, ?string $stepKey): void
    {
        $session->update(['current_step_key' => $stepKey]);
    }

    /**
     * Perbarui data pendaftaran sementara pada sesi.
     */
    public function updateRegistrationData(ChatbotSession $session, array $data): void
    {
        $currentData = $session->registration_data ?? [];
        $session->update(['registration_data' => array_merge($currentData, $data)]);
    }

    /**
     * Reset sesi pendaftaran.
     */
    public function resetSession(ChatbotSession $session): void
    {
        $session->update([
            'current_step_key' => null,
            'registration_data' => [],
            'last_activity_at' => now(),
        ]);
    }
}
