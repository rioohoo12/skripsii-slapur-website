<?php

namespace App\Services\Chatbot;

class Preprocessor
{
    /**
     * Bersihkan dan normalisasi teks input dari user.
     */
    public function process(string $rawMessage): string
    {
        // 1. Trim spasi awal/akhir
        $clean = trim($rawMessage);

        // 2. Hapus tag HTML jika ada
        $clean = strip_tags($clean);

        // 3. Normalisasi multiple whitespace menjadi single space
        $clean = preg_replace('/\s+/', ' ', $clean);

        return $clean;
    }

    /**
     * Dapatkan versi lowercase untuk pengujian keyword/intent.
     */
    public function toNormalizedLower(string $rawMessage): string
    {
        return mb_strtolower($this->process($rawMessage));
    }
}
