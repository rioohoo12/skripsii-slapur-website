<?php

namespace App\Services\Chatbot\Dialog\Flows;

use App\Services\Chatbot\Contracts\FlowInterface;
use App\Models\ChatSession;

class PaymentFlow implements FlowInterface
{
    protected $slots = [
        'nomor_pendaftaran' => ['question' => 'Untuk memproses pembayaran, silakan ketik Nomor Pendaftaran Anda (contoh: REG-1234).'],
        'metode_bayar' => ['question' => 'Nomor Pendaftaran {nomor_pendaftaran} ditemukan. Tagihan: Rp 250.000. Metode pembayaran apa yang ingin Anda gunakan? (Transfer Bank / e-Wallet)'],
        'bukti_pembayaran' => ['question' => 'Silakan transfer ke BSI 123456789 a.n. SLAPUR. Jika sudah, ketik "sudah transfer" atau unggah bukti pembayaran Anda di sini.']
    ];

    public function handle(ChatSession $session, string $intent, array $entities, string $text): array
    {
        $currentSlots = json_decode($session->slots, true) ?: [];
        
        // 1. Ambil/merge entitas
        foreach ($entities as $key => $val) {
            if (array_key_exists($key, $this->slots)) {
                $currentSlots[$key] = $val;
            }
        }

        // Tangkap nomor pendaftaran pakai regex jika tidak ditangkap oleh Extractor
        if ($session->current_step === 'nomor_pendaftaran' && !isset($currentSlots['nomor_pendaftaran'])) {
            if (preg_match('/REG-\d+/i', $text, $matches)) {
                $currentSlots['nomor_pendaftaran'] = strtoupper($matches[0]);
            } else {
                // Asumsi text adalah nomor jika sangat pendek
                if (strlen($text) < 10) {
                     $currentSlots['nomor_pendaftaran'] = 'REG-' . preg_replace('/\D/', '', $text);
                }
            }
        }

        // Tangkap konfirmasi bukti pembayaran
        if ($session->current_step === 'bukti_pembayaran') {
            if (preg_match('/\b(sudah|beres|done|ok)\b/i', strtolower($text))) {
                $currentSlots['bukti_pembayaran'] = 'uploaded'; // Simulasi upload
            }
        }

        // 3. Slot filling logic
        foreach ($this->slots as $slotKey => $slotConfig) {
            if (!isset($currentSlots[$slotKey])) {
                $session->current_step = $slotKey;
                $session->slots = json_encode($currentSlots);
                
                $question = $slotConfig['question'];
                foreach ($currentSlots as $k => $v) {
                    $question = str_replace('{' . $k . '}', $v, $question);
                }
                
                return [
                    'reply' => $question,
                    'action' => $slotKey === 'bukti_pembayaran' ? 'show_upload_form' : null // Trigger form di frontend Vue
                ];
            }
        }

        // 4. All slots filled -> Eksekusi aksi pembayaran
        if (isset($currentSlots['bukti_pembayaran'])) {
            $session->current_flow = 'default';
            $session->current_step = 'init';
            
            return [
                'reply' => 'Terima kasih! Bukti pembayaran untuk pendaftaran ' . $currentSlots['nomor_pendaftaran'] . ' telah kami terima dan sedang diverifikasi oleh admin. Kami akan memberitahu Anda setelah diverifikasi.',
                'action' => 'payment_submitted'
            ];
        }

        return ['reply' => 'Silakan lengkapi instruksi sebelumnya.'];
    }
}
