<?php

namespace App\Services\Chatbot\Dialog\Flows;

use App\Services\Chatbot\Contracts\FlowInterface;
use App\Models\ChatSession;

class RegistrationFlow implements FlowInterface
{
    protected $slots = [
        'nama_lengkap' => ['question' => 'Mari kita mulai! Siapa nama lengkap Anda?'],
        'jenis_kelamin' => ['question' => 'Baik {nama_lengkap}, apa jenis kelamin Anda? (Laki-laki / Perempuan)'],
        'tanggal_lahir' => ['question' => 'Kapan tanggal lahir Anda? (Format: DD-MM-YYYY)'],
        'no_hp' => ['question' => 'Berapa nomor HP/WA Anda yang aktif?'],
        'email' => ['question' => 'Apa alamat email Anda?'],
        'jenjang' => ['question' => 'Anda mendaftar untuk jenjang apa? (SMP / SMA)'],
        'tipe_kamar' => ['question' => 'Pilih tipe kamar asrama: (Standard / VIP)']
    ];

    public function handle(ChatSession $session, string $intent, array $entities, string $text): array
    {
        $currentSlots = json_decode($session->slots, true) ?: [];
        
        // 1. Merge new entities into slots
        foreach ($entities as $key => $val) {
            if (array_key_exists($key, $this->slots) || $key === 'koreksi') {
                $currentSlots[$key] = $val;
            }
        }

        // 2. Handle intent "koreksi_data"
        if ($intent === 'koreksi_data') {
            $session->current_step = 'koreksi';
            return ['reply' => 'Baik, bagian mana yang ingin diubah? (nama, hp, email, dll)'];
        }

        // 3. Slot filling logic
        foreach ($this->slots as $slotKey => $slotConfig) {
            if (!isset($currentSlots[$slotKey])) {
                $session->current_step = $slotKey;
                $session->slots = json_encode($currentSlots);
                
                // Dynamic variable replacement in question
                $question = $slotConfig['question'];
                foreach ($currentSlots as $k => $v) {
                    $question = str_replace('{' . $k . '}', $v, $question);
                }
                
                return ['reply' => $question];
            }
        }

        // 4. All slots filled, awaiting confirmation
        if ($session->current_step !== 'konfirmasi_akhir') {
            $session->current_step = 'konfirmasi_akhir';
            $session->slots = json_encode($currentSlots);
            
            $summary = "Terima kasih! Berikut rangkuman data Anda:\n";
            foreach ($this->slots as $key => $config) {
                $summary .= "- " . ucwords(str_replace('_', ' ', $key)) . ": " . $currentSlots[$key] . "\n";
            }
            $summary .= "\nApakah data ini sudah benar? (Ya / Tidak)";
            
            return ['reply' => $summary];
        }

        // 5. Final Confirmation
        if ($intent === 'konfirmasi' || strtolower(trim($text)) === 'ya') {
            $session->current_flow = 'default';
            $session->current_step = 'init';
            // Here we would trigger RegistrationAction to save to DB
            return [
                'reply' => 'Pendaftaran berhasil dicatat! Nomor pendaftaran Anda adalah: REG-' . rand(1000, 9999) . '. Silakan ketik "bayar" untuk melihat instruksi pembayaran.',
                'action' => 'register_applicant'
            ];
        }

        return ['reply' => 'Maaf, silakan jawab "Ya" jika data benar, atau "koreksi" jika ada yang salah.'];
    }
}
