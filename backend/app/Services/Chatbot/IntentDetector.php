<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotAnswer;
use App\Models\ChatbotSession;

class IntentDetector
{
    public const INTENT_REGISTRATION_START = 'registration_start';
    public const INTENT_REGISTRATION_STEP  = 'registration_step';
    public const INTENT_DOCUMENT_UPLOAD   = 'document_upload';
    public const INTENT_SCHEDULE_INQUIRY  = 'schedule_inquiry';
    public const INTENT_DINING_INQUIRY    = 'dining_inquiry';
    public const INTENT_FAQ_MATCH         = 'faq_match';
    public const INTENT_UNKNOWN           = 'unknown';

    /**
     * Deteksi intent pesan berdasarkan konteks sesi dan teks yang sudah dinormalisasi.
     */
    public function detect(string $normalizedLower, ChatbotSession $session): array
    {
        $currentStep = $session->current_step_key;

        // 1. Jika pengguna sedang dalam alur pendaftaran aktif
        if (in_array($currentStep, ['profile', 'payment', 'kamar', 'kurikulum', 'dokumen'], true)) {
            return [
                'intent' => self::INTENT_REGISTRATION_STEP,
                'step_key' => $currentStep,
                'answer' => null,
            ];
        }

        // 2. Deteksi intent pendaftaran awal (contoh: "1" atau "cara daftar")
        if (in_array($normalizedLower, ['1', 'cara daftar', 'cara daftar?'], true) || str_contains($normalizedLower, 'cara daftar')) {
            return [
                'intent' => self::INTENT_REGISTRATION_START,
                'step_key' => 'start',
                'answer' => null,
            ];
        }

        // 3. Deteksi intent Jadwal Pelajaran
        if (str_contains($normalizedLower, 'jadwal') || str_contains($normalizedLower, 'pelajaran')) {
            return [
                'intent' => self::INTENT_SCHEDULE_INQUIRY,
                'step_key' => null,
                'answer' => null,
            ];
        }

        // 4. Deteksi intent Dining / Nomor Makan
        if (str_contains($normalizedLower, 'makan') || str_contains($normalizedLower, 'dining') || str_contains($normalizedLower, 'kantin') || str_contains($normalizedLower, 'nomor makan')) {
            return [
                'intent' => self::INTENT_DINING_INQUIRY,
                'step_key' => null,
                'answer' => null,
            ];
        }

        // 5. Deteksi intent Upload Dokumen
        if (str_contains($normalizedLower, 'upload') || str_contains($normalizedLower, 'dokumen') || str_contains($normalizedLower, 'ijazah') || str_contains($normalizedLower, 'akte')) {
            return [
                'intent' => self::INTENT_DOCUMENT_UPLOAD,
                'step_key' => null,
                'answer' => null,
            ];
        }

        // 6. Pencocokan keyword FAQ dari basis data ChatbotAnswer
        $answer = $this->matchFaqAnswer($session, $normalizedLower);
        if ($answer) {
            return [
                'intent' => self::INTENT_FAQ_MATCH,
                'step_key' => $answer->step_key,
                'answer' => $answer,
            ];
        }

        return [
            'intent' => self::INTENT_UNKNOWN,
            'step_key' => null,
            'answer' => null,
        ];
    }

    private function matchFaqAnswer(ChatbotSession $session, string $text): ?ChatbotAnswer
    {
        $currentStep = $session->current_step_key;

        $query = ChatbotAnswer::where('topic', 'pendaftaran');
        if ($currentStep === null) {
            $query->where('step_key', '!=', 'verifikasi_email');
        } else {
            $query->where(function ($q) use ($currentStep) {
                $q->whereNull('step_key')->orWhere('step_key', $currentStep);
            });
        }

        $answers = $query->orderBy('order')->get();

        foreach ($answers as $answer) {
            $keywords = $answer->trigger_keywords ?? [];
            if (empty($keywords) && $answer->step_key === 'verifikasi_email') {
                continue;
            }
            foreach ($keywords as $keyword) {
                if (str_contains($text, mb_strtolower($keyword))) {
                    return $answer;
                }
            }
        }

        return null;
    }
}
