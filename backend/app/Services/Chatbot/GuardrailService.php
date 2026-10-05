<?php

namespace App\Services\Chatbot;

class GuardrailService
{
    /**
     * Pola prompt injection & kata kunci terlarang.
     */
    protected array $injectionPatterns = [
        '/ignore\s+previous\s+instructions/i',
        '/ignore\s+all\s+rules/i',
        '/forget\s+all\s+prior/i',
        '/you\s+are\s+now\s+a\s+DAN/i',
        '/override\s+system\s+prompt/i',
        '/<script\b[^>]*>/i',
        '/union\s+select\s+/i',
        '/drop\s+table\s+/i',
        '/select\s+.*\s+from\s+users/i',
    ];

    /**
     * Validasi pesan pengguna terhadap aturan keamanan dan batasan topik.
     */
    public function validate(string $message): array
    {
        $cleanText = trim($message);

        // 1. Batas panjang pesan (Maksimal 500 karakter)
        if (mb_strlen($cleanText) > 500) {
            return [
                'is_valid' => false,
                'reason' => 'Pesan Anda terlalu panjang. Maksimal 500 karakter per pesan.',
            ];
        }

        if (mb_strlen($cleanText) < 1) {
            return [
                'is_valid' => false,
                'reason' => 'Pesan tidak boleh kosong.',
            ];
        }

        // 2. Deteksi Prompt Injection & Malicious Script
        foreach ($this->injectionPatterns as $pattern) {
            if (preg_match($pattern, $cleanText)) {
                return [
                    'is_valid' => false,
                    'reason' => 'Permintaan Anda terdeteksi mengandung instruksi yang tidak diizinkan demi keamanan sistem.',
                ];
            }
        }

        return [
            'is_valid' => true,
            'reason' => null,
        ];
    }
}
