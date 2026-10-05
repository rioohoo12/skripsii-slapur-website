<?php

namespace App\Services\Chatbot;

class PreprocessingService
{
    /**
     * Slang & synonym dictionary for normalization.
     */
    protected array $slangMap = [
        'gak' => 'tidak',
        'nomer' => 'nomor',
        'no' => 'nomor',
        'telp' => 'telepon',
        'hp' => 'telepon',
        'wa' => 'telepon',
        'makasi' => 'terima kasih',
        'makasih' => 'terima kasih',
        'thx' => 'terima kasih',
        'thanks' => 'terima kasih',
        'ortu' => 'orang tua',
        'bapak' => 'ayah',
        'bunda' => 'ibu',
        'kamar' => 'kamar',
        'spp' => 'spp',
        'rapot' => 'nilai',
        'rapor' => 'nilai',
    ];

    /**
     * Clean and normalize user text.
     */
    public function preprocess(string $text): array
    {
        $clean = trim(preg_replace('/\s+/u', ' ', $text));
        $lower = mb_strtolower($clean);

        // Normalize words
        $words = explode(' ', $lower);
        $normalizedWords = [];
        foreach ($words as $word) {
            $normalizedWords[] = $this->slangMap[$word] ?? $word;
        }
        $normalizedText = implode(' ', $normalizedWords);

        // Intent detection based on intent map
        $intent = $this->detectIntent($normalizedText, $lower);

        return [
            'raw' => $text,
            'clean' => $clean,
            'normalized' => $normalizedText,
            'intent' => $intent,
        ];
    }

    /**
     * Classify user intent based on finalized scope.
     */
    protected function detectIntent(string $normalizedText, string $lower): string
    {
        if (preg_match('/\b(staf|staff|admin|cs|operator|petugas|manusia)\b/u', $lower)) {
            return 'staf';
        }

        if (preg_match('/\b(batal|cancel|berhenti)\b/u', $lower)) {
            return 'batal';
        }

        if (preg_match('/\b(tagihan|bayar|pembayaran|spp|invoice|biaya|rekening|transfer)\b/u', $lower)) {
            if (preg_match('/\b(status|sudah|cek|verifikasi|lunas)\b/u', $lower)) {
                return 'status_pembayaran';
            }
            return 'tagihan_dan_bayar';
        }

        if (preg_match('/\b(nilai|rapor|rapot|khs|ipk|skor|ujian|hasil\s+belajar)\b/u', $lower)) {
            return 'nilai';
        }

        if (preg_match('/\b(absensi|absen|kehadiran|presensi)\b/u', $lower)) {
            return 'absensi';
        }

        if (preg_match('/\b(jadwal|jam\s+pelajaran|jam\s+mengajar|kegiatan)\b/u', $lower)) {
            return 'jadwal';
        }

        if (preg_match('/\b(kamar|asrama|tipe\s+kamar|bed|ranjang)\b/u', $lower)) {
            return 'pilih_kamar';
        }

        if (preg_match('/\b(materi|modul|tugas|bahan\s+ajar)\b/u', $lower)) {
            return 'materi';
        }

        if (preg_match('/\b(syarat|info\s+daftar|informasi\s+pendaftaran|ppdb|kapan\s+buka|jadwal\s+pendaftaran)\b/u', $lower)) {
            return 'info_pendaftaran';
        }

        if (preg_match('/\b(daftar|mendaftar|registrasi|isi\s+form|formulir)\b/u', $lower)) {
            return 'daftar';
        }

        if (preg_match('/\b(faq|aturan|jam\s+layanan|kontak|alamat|visi|misi)\b/u', $lower)) {
            return 'faq';
        }

        return 'tidak_dikenali';
    }
}
