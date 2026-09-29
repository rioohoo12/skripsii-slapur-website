<?php

namespace App\Services\Chatbot\Fallback;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LLMFallback
{
    /**
     * Panggil LLM (GPT) ketika NLP core memiliki confidence rendah
     */
    public function generateResponse(string $question, array $contextHistory = []): string
    {
        if (!config('chatbot.enable_llm_fallback')) {
            return "Maaf, saya tidak mengerti maksud Anda. Ketik 'faq' untuk bantuan atau 'staf' untuk bicara dengan admin.";
        }

        // Limit the context history to user messages to save tokens and prevent confusion
        $recentChat = array_map(function($msg) {
            return $msg['role'] . ': ' . $msg['text'];
        }, $contextHistory);
        $historyText = implode("\n", $recentChat);

        $systemPrompt = "Anda adalah AI Assistant pendaftaran siswa baru SLAPUR (Sekolah).
Tugas Anda HANYA menjawab pertanyaan seputar pendaftaran, biaya, asrama, dan panduan penggunaan sistem.
ATURAN KERAS:
1. JANGAN pernah mengarang jadwal ujian, tanggal spesifik, atau rincian biaya yang tidak Anda ketahui pasti. Jika tidak tahu, suruh user bertanya ke admin.
2. Jawab dengan ramah, sangat singkat, dan jelas (maksimal 2-3 kalimat).
3. Jika user bertanya hal di luar sekolah/pendaftaran, tolak dengan sopan.
Biaya pendaftaran saat ini adalah Rp 250.000. Asrama wajib bagi siswa luar kota.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ])->timeout(8)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Riwayat Chat:\n" . $historyText . "\n\nPertanyaan User: " . $question],
                ],
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? "Mohon maaf, sistem AI kami sedang sibuk. Silakan coba lagi nanti.";
            }

            Log::error('OpenAI API Error: ' . $response->body());
            return "Maaf, saya sedang mengalami kendala jaringan ke otak pusat. Ketik 'staf' untuk bantuan langsung.";

        } catch (\Exception $e) {
            Log::error('LLMFallback Exception: ' . $e->getMessage());
            return "Maaf, sistem sedang sibuk. Silakan hubungi admin.";
        }
    }
}
