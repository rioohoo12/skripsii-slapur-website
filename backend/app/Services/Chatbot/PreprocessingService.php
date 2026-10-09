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
        // 1. Website Guidance
        if (preg_match('/(cara\s+gunakan\s+website|cara\s+pakai|panduan\s+website|fitur\s+website|navigasi|bagaimana\s+menggunakan)/u', $lower)) {
            return 'cara_gunakan_website';
        }

        // 2. Staff Contact & Support
        if (preg_match('/(staf|staff|bicara\s+dg\s+staf|bantuan\s+staf|hubungi\s+staf|admin|cs|operator|petugas|manusia|kontak)/u', $lower)) {
            return 'staf';
        }

        // 3. Cancel / Reset
        if (preg_match('/(batal|cancel|berhenti|mulai\s+ulang|reset)/u', $lower)) {
            return 'batal';
        }

        // 4. Financial & Payments
        if (preg_match('/(tagihan|bayar|pembayaran|spp|invoice|biaya|rekening|transfer|tagihan\s+saya)/u', $lower)) {
            if (preg_match('/(status|sudah|cek|verifikasi|lunas)/u', $lower)) {
                return 'status_pembayaran';
            }
            return 'tagihan_dan_bayar';
        }

        // 5. Grades & Academic Scores
        if (preg_match('/(nilai|rapor|rapot|khs|ipk|skor|ujian|hasil\s+belajar|panduan\s+input\s+nilai)/u', $lower)) {
            if (preg_match('/(input|panduan\s+input)/u', $lower)) {
                return 'panduan_nilai';
            }
            return 'nilai';
        }

        // 6. Attendance & Presensi
        if (preg_match('/(absensi|absen|kehadiran|presensi|panduan\s+input\s+absensi)/u', $lower)) {
            if (preg_match('/(input|panduan\s+input)/u', $lower)) {
                return 'panduan_absensi';
            }
            return 'absensi';
        }

        // 7. Class Schedules & Classes
        if (preg_match('/(jadwal|jam\s+pelajaran|jadwal\s+mengajar|jadwal\s+pelajaran|kegiatan)/u', $lower)) {
            return 'jadwal';
        }

        if (preg_match('/(daftar\s+kelas|data\s+kelas|kelas)/u', $lower)) {
            return 'daftar_kelas';
        }

        // 8. Dormitory & Rooms
        if (preg_match('/(kamar|asrama|aturan\s+asrama|tipe\s+kamar|bed|ranjang|ketersediaan\s+kamar)/u', $lower)) {
            return 'pilih_kamar';
        }

        // 9. Materials
        if (preg_match('/(materi|modul|tugas|bahan\s+ajar)/u', $lower)) {
            return 'materi';
        }

        // 10. Registration & Applications
        if (preg_match('/(syarat|info\s+daftar|informasi\s+pendaftaran|panduan\s+pendaftaran|ppdb|kapan\s+buka|jadwal\s+pendaftaran)/u', $lower)) {
            return 'info_pendaftaran';
        }

        if (preg_match('/(daftar|mendaftar|registrasi|isi\s+form|formulir)/u', $lower)) {
            return 'daftar';
        }

        // 11. Staff & Admin Guides
        if (preg_match('/(panduan\s+verifikasi|verifikasi\s+pembayaran)/u', $lower)) {
            return 'panduan_verifikasi';
        }

        if (preg_match('/(ringkasan\s+siswa|ringkasan\s+data\s+siswa)/u', $lower)) {
            return 'ringkasan_data_siswa';
        }

        if (preg_match('/(panduan\s+kelola\s+role|kelola\s+role)/u', $lower)) {
            return 'panduan_kelola_role';
        }

        if (preg_match('/(ringkasan\s+monitoring|monitoring)/u', $lower)) {
            return 'ringkasan_monitoring';
        }

        // 12. General FAQ
        if (preg_match('/(faq|aturan|jam\s+layanan|alamat|visi|misi|sekolah)/u', $lower)) {
            return 'faq';
        }

        return 'tidak_dikenali';
    }
}
