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
        // 1. Role Menu Guidance Intents
        if (preg_match('/(panduan\s+menu\s+administrasi|fungsi\s+menu\s+administrasi|menu\s+administrasi)/u', $lower)) {
            return 'panduan_menu_administrasi';
        }
        if (preg_match('/(panduan\s+menu\s+asrama\s+putra|fungsi\s+menu\s+asrama\s+putra|menu\s+asrama\s+putra)/u', $lower)) {
            return 'panduan_menu_asrama_putra';
        }
        if (preg_match('/(panduan\s+menu\s+asrama\s+putri|fungsi\s+menu\s+asrama\s+putri|menu\s+asrama\s+putri)/u', $lower)) {
            return 'panduan_menu_asrama_putri';
        }
        if (preg_match('/(panduan\s+menu\s+kafetaria|fungsi\s+menu\s+kafetaria|menu\s+kafetaria|fitur\s+kafetaria)/u', $lower)) {
            return 'panduan_menu_kafetaria';
        }
        if (preg_match('/(panduan\s+menu\s+siswa|fungsi\s+menu\s+siswa|menu\s+siswa)/u', $lower)) {
            return 'panduan_menu_siswa';
        }

        // 2. Staff Administrasi: Info Siswa & Verifikasi
        if (preg_match('/(cari\s+info\s+siswa|info\s+siswa|cari\s+siswa|data\s+siswa|data\s+murid|profil\s+siswa|biodata\s+siswa|cek\s+siswa|detail\s+siswa|informasi\s+siswa)/u', $lower)) {
            return 'cari_info_siswa';
        }
        if (preg_match('/(berkas\s+pending|dokumen\s+pending|berkas\s+menunggu\s+verifikasi|status\s+verifikasi\s+berkas|dokumen\s+belum\s+diverifikasi)/u', $lower)) {
            return 'status_verifikasi_berkas';
        }
        if (preg_match('/(antrean\s+pembayaran|pembayaran\s+pending|pembayaran\s+masuk|verifikasi\s+pembayaran)/u', $lower)) {
            return 'status_pembayaran_administrasi';
        }
        if (preg_match('/(ringkasan\s+pendaftar|ringkasan\s+siswa|ringkasan\s+data\s+siswa|total\s+pendaftar|statistik\s+siswa)/u', $lower)) {
            return 'ringkasan_data_siswa';
        }

        // 3. Staff Asrama Putra & Putri
        if (preg_match('/(total\s+murid\s+asrama\s+putra|total\s+siswa\s+asrama\s+putra|penghuni\s+asrama\s+putra)/u', $lower)) {
            return 'total_murid_asrama_putra';
        }
        if (preg_match('/(total\s+murid\s+asrama\s+putri|total\s+siswa\s+asrama\s+putri|penghuni\s+asrama\s+putri)/u', $lower)) {
            return 'total_murid_asrama_putri';
        }
        if (preg_match('/(total\s+murid\s+asrama|total\s+murid\s+di\s+asrama|penghuni\s+asrama|rekap\s+asrama|kapasitas\s+asrama)/u', $lower)) {
            return 'total_murid_asrama';
        }
        if (preg_match('/(ketersediaan\s+kamar|kamar\s+kosong|sisa\s+kamar|kamar\s+tersedia)/u', $lower)) {
            return 'ketersediaan_kamar_asrama';
        }
        if (preg_match('/(persetujuan\s+kamar|antrean\s+persetujuan|approval\s+kamar|pendaftar\s+kamar\s+baru|permintaan\s+kamar)/u', $lower)) {
            return 'persetujuan_kamar_asrama';
        }
        if (preg_match('/(panduan\s+mutasi|pindah\s+kamar|cara\s+mutasi\s+kamar|mutasi\s+kamar)/u', $lower)) {
            return 'panduan_mutasi_asrama';
        }

        // 4. Staff Kafetaria
        if (preg_match('/(menu\s+makanan\s+hari\s+ini|menu\s+hari\s+ini|makanan\s+hari\s+ini|jadwal\s+menu|sarapan\s+apa|makan\s+siang\s+apa|makan\s+malam\s+apa|menu\s+dining)/u', $lower)) {
            return 'menu_kafetaria_hari_ini';
        }
        if (preg_match('/(rekap\s+siswa\s+makan|rekap\s+makan|siapa\s+sudah\s+makan|siswa\s+sudah\s+makan|total\s+makan\s+hari\s+ini|porsi\s+hari\s+ini|antrean\s+makan)/u', $lower)) {
            return 'rekap_siswa_makan';
        }
        if (preg_match('/(panduan\s+scan|scan\s+kartu\s+makan|cara\s+scan|scan\s+barcode|scan\s+qr)/u', $lower)) {
            return 'panduan_scan_makan';
        }
        if (preg_match('/(input\s+menu|tambah\s+menu|panduan\s+input\s+menu|cara\s+ubah\s+menu)/u', $lower)) {
            return 'panduan_input_menu';
        }

        // 5. Website Guidance & Support
        if (preg_match('/(cara\s+gunakan\s+website|cara\s+pakai|panduan\s+website|fitur\s+website|navigasi|bagaimana\s+menggunakan)/u', $lower)) {
            return 'cara_gunakan_website';
        }

        // 6. Staff Contact & Support
        if (preg_match('/(staf|staff|bicara\s+dg\s+staf|bantuan\s+staf|hubungi\s+staf|admin|cs|operator|petugas|manusia|kontak)/u', $lower)) {
            return 'staf';
        }

        // 7. Cancel / Reset
        if (preg_match('/(batal|cancel|berhenti|mulai\s+ulang|reset)/u', $lower)) {
            return 'batal';
        }

        // 8. Financial & Payments (Siswa)
        if (preg_match('/(tagihan|bayar|pembayaran|spp|invoice|biaya|rekening|transfer|tagihan\s+saya)/u', $lower)) {
            if (preg_match('/(status|sudah|cek|verifikasi|lunas)/u', $lower)) {
                return 'status_pembayaran';
            }
            return 'tagihan_dan_bayar';
        }

        // 9. Grades & Academic Scores (Guru & Siswa)
        if (preg_match('/(nilai|rapor|rapot|khs|ipk|skor|ujian|hasil\s+belajar|panduan\s+input\s+nilai)/u', $lower)) {
            if (preg_match('/(input|panduan\s+input)/u', $lower)) {
                return 'panduan_nilai';
            }
            return 'nilai';
        }

        // 10. Attendance & Presensi (Guru & Siswa)
        if (preg_match('/(absensi|absen|kehadiran|presensi|panduan\s+input\s+absensi)/u', $lower)) {
            if (preg_match('/(input|panduan\s+input)/u', $lower)) {
                return 'panduan_absensi';
            }
            return 'absensi';
        }

        // 11. Class Schedules & Classes
        if (preg_match('/(jadwal|jam\s+pelajaran|jadwal\s+mengajar|jadwal\s+pelajaran|kegiatan)/u', $lower)) {
            return 'jadwal';
        }

        if (preg_match('/(daftar\s+kelas|data\s+kelas|kelas)/u', $lower)) {
            return 'daftar_kelas';
        }

        // 12. Dormitory & Rooms (General)
        if (preg_match('/(kamar|asrama|aturan\s+asrama|tipe\s+kamar|bed|ranjang)/u', $lower)) {
            return 'pilih_kamar';
        }

        // 13. Materials
        if (preg_match('/(materi|modul|tugas|bahan\s+ajar)/u', $lower)) {
            return 'materi';
        }

        // 14. Registration & Applications
        if (preg_match('/(syarat|info\s+daftar|informasi\s+pendaftaran|panduan\s+pendaftaran|ppdb|kapan\s+buka|jadwal\s+pendaftaran)/u', $lower)) {
            return 'info_pendaftaran';
        }

        if (preg_match('/(daftar|mendaftar|registrasi|isi\s+form|formulir)/u', $lower)) {
            return 'daftar';
        }

        // 15. Admin Guides
        if (preg_match('/(panduan\s+verifikasi|verifikasi\s+pembayaran)/u', $lower)) {
            return 'panduan_verifikasi';
        }

        if (preg_match('/(panduan\s+kelola\s+role|kelola\s+role)/u', $lower)) {
            return 'panduan_kelola_role';
        }

        if (preg_match('/(ringkasan\s+monitoring|monitoring)/u', $lower)) {
            return 'ringkasan_monitoring';
        }

        // 16. General FAQ
        if (preg_match('/(faq|aturan|jam\s+layanan|alamat|visi|misi|sekolah)/u', $lower)) {
            return 'faq';
        }

        return 'tidak_dikenali';
    }
}
