<?php

namespace App\Services\Chatbot;

use App\Models\KnowledgeBase;
use App\Models\User;

class IntentRouter
{
    public function __construct(
        protected LocalDataResolver $localDataResolver,
        protected FormAssistant $formAssistant
    ) {}

    /**
     * Determines the execution route: 'action', 'local_data', 'faq', or 'llm'.
     */
    public function route(string $intent, string $message, ?User $user, array $slots = []): array
    {
        $role = strtolower($user?->role ?? 'umum');

        // 1. Intent pendaftaran -> Rule-based Form Assistant (Action)
        if ($intent === 'daftar' || ($slots['_mode'] ?? null) === 'register') {
            return [
                'type' => 'action',
                'action_type' => 'form_assistant',
                'intent' => 'daftar',
            ];
        }

        // 2. Intent Data Lokal (Nilai, Absensi, Tagihan, Jadwal, Kamar)
        if (in_array($intent, ['nilai', 'absensi', 'tagihan_dan_bayar', 'status_pembayaran', 'pilih_kamar', 'jadwal', 'materi', 'ringkasan_data_siswa'])) {
            $resolvedData = $this->localDataResolver->resolve($intent, $user, $slots);
            if ($resolvedData['has_data']) {
                return [
                    'type' => 'local_data',
                    'intent' => $intent,
                    'resolved_data' => $resolvedData,
                ];
            }
        }

        // 3. Intent FAQ / Knowledge Base Match
        $kbMatch = $this->findKnowledgeBaseMatch($message, $role);
        if ($kbMatch) {
            return [
                'type' => 'faq',
                'intent' => 'faq',
                'answer' => $kbMatch->jawaban,
            ];
        }

        // 4. Fallback ke LLM Router
        return [
            'type' => 'llm',
            'intent' => $intent,
        ];
    }

    /**
     * Find exact or keyword match from knowledge_base table.
     */
    protected function findKnowledgeBaseMatch(string $message, string $role): ?KnowledgeBase
    {
        $lower = mb_strtolower($message);

        $items = KnowledgeBase::where('is_active', true)
            ->where(function ($q) use ($role) {
                $q->where('role_akses', 'semua')->orWhere('role_akses', $role);
            })->get();

        foreach ($items as $item) {
            $qLower = mb_strtolower($item->pertanyaan);
            if (str_contains($lower, $qLower) || str_contains($qLower, $lower)) {
                return $item;
            }
        }

        return null;
    }
}
