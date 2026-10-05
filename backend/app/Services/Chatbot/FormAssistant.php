<?php

namespace App\Services\Chatbot;

use App\Models\Fee;
use App\Models\KnowledgeItem;

/**
 * Custom (rule-based) chatbot untuk membantu mengisi formulir pendaftaran.
 *
 * Tidak memakai OpenAI maupun layanan Python eksternal: seluruh NLU
 * (deteksi intent, ekstraksi entitas, slot filling) dijalankan di PHP/Laravel.
 *
 * Antarmuka sengaja dibuat sama dengan layanan GPT sebelumnya:
 *   getResponse(array $history, string $message, array $slots): array
 *   => ['intent' => ..., 'reply' => ..., 'slots' => [...]]
 *
 * Slot yang dikembalikan adalah STATE LENGKAP (termasuk _mode & _awaiting)
 * sehingga pemanggil cukup menyimpannya apa adanya.
 */
class FormAssistant
{
    /** Urutan pertanyaan = urutan kolom formulir. */
    protected const SLOTS = [
        'nama'           => 'Siapa nama lengkap calon siswa? (sesuai akte kelahiran)',
        'jenis_kelamin'  => 'Apa jenis kelamin calon siswa? (Laki-laki / Perempuan)',
        'tempat_lahir'   => 'Di kota mana calon siswa lahir?',
        'tanggal_lahir'  => 'Kapan tanggal lahirnya? (contoh: 12-08-2008 atau 12 Agustus 2008)',
        'agama'          => 'Apa agama calon siswa?',
        'alamat'         => 'Apa alamat lengkap tempat tinggal? (jalan, RT/RW, desa/kelurahan)',
        'kelas'          => 'Mendaftar ke kelas berapa? (7, 8, 9 untuk SMP atau 10, 11, 12 untuk SMA)',
        'asal_sekolah'   => 'Dari sekolah mana asalnya? (ketik "-" jika belum ada)',
        'nama_ayah'      => 'Siapa nama ayah / wali calon siswa?',
        'pekerjaan_ayah' => 'Apa pekerjaan ayah / wali?',
        'nama_ibu'       => 'Siapa nama ibu calon siswa?',
        'pekerjaan_ibu'  => 'Apa pekerjaan ibu?',
        'telepon'        => 'Berapa nomor HP/WhatsApp orang tua / wali yang aktif? (awalan 08 atau 62)',
    ];

    protected const LABELS = [
        'nama'           => 'Nama Lengkap',
        'jenis_kelamin'  => 'Jenis Kelamin',
        'tempat_lahir'   => 'Tempat Lahir',
        'tanggal_lahir'  => 'Tanggal Lahir',
        'agama'          => 'Agama',
        'alamat'         => 'Alamat',
        'kelas'          => 'Kelas Pendaftaran',
        'asal_sekolah'   => 'Asal Sekolah',
        'nama_ayah'      => 'Nama Ayah/Wali',
        'pekerjaan_ayah' => 'Pekerjaan Ayah/Wali',
        'nama_ibu'       => 'Nama Ibu',
        'pekerjaan_ibu'  => 'Pekerjaan Ibu',
        'telepon'        => 'No. HP Orang Tua/Wali',
    ];

    /** Kata kunci untuk memilih bagian mana yang ingin dikoreksi. */
    protected const CORRECTION_KEYWORDS = [
        'pekerjaan_ayah' => ['pekerjaan ayah', 'kerja ayah', 'pekerjaan bapak', 'pekerjaan wali'],
        'pekerjaan_ibu'  => ['pekerjaan ibu', 'kerja ibu'],
        'nama_ayah'      => ['nama ayah', 'nama bapak', 'nama wali', 'ayah', 'bapak', 'wali'],
        'nama_ibu'       => ['nama ibu', 'ibu'],
        'tempat_lahir'   => ['tempat lahir', 'kota lahir'],
        'tanggal_lahir'  => ['tanggal lahir', 'tgl lahir', 'lahir'],
        'jenis_kelamin'  => ['jenis kelamin', 'gender', 'kelamin'],
        'asal_sekolah'   => ['asal sekolah', 'sekolah'],
        'telepon'        => ['telepon', 'telpon', 'hp', 'wa', 'whatsapp', 'nomor', 'no'],
        'alamat'         => ['alamat', 'rumah', 'tinggal'],
        'agama'          => ['agama'],
        'kelas'          => ['kelas', 'jenjang', 'tingkat'],
        'nama'           => ['nama'],
    ];

    protected const MONTHS = [
        'januari' => 1, 'jan' => 1, 'februari' => 2, 'feb' => 2, 'maret' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4, 'mei' => 5, 'juni' => 6, 'jun' => 6, 'juli' => 7, 'jul' => 7,
        'agustus' => 8, 'agu' => 8, 'agt' => 8, 'ags' => 8, 'september' => 9, 'sep' => 9, 'sept' => 9,
        'oktober' => 10, 'okt' => 10, 'november' => 11, 'nov' => 11, 'desember' => 12, 'des' => 12,
    ];

    /** Pembatas akhir sebuah nilai berlabel (koma, titik koma, baris baru, atau label berikutnya). */
    protected const TERM = '\s*(?:,|;|\n|$)|\s+(?:dan|lahir|tempat|tanggal|alamat|asal|agama|nama|laki|perempuan|kelas|nomor|no\b|hp|wa\b|pekerjaan|ayah|ibu)\b|\s+(?:pada\s+)?(?:tanggal\s+)?\d{1,2}[\s\/\-\.]\w';

    protected const ADDRESS_TERM = '\s*(?:;|\n|$)|\s+(?:asal\s+sekolah|nama|tanggal\s+lahir|agama|kelas|nomor|no\s+hp|pekerjaan)\b';

    // ------------------------------------------------------------------ API

    public function getResponse(array $history, string $message, array $slots = []): array
    {
        $text  = trim(preg_replace('/\s+/u', ' ', $message));
        $lower = mb_strtolower($text);

        $mode     = $slots['_mode'] ?? null;      // null | register | done
        $awaiting = $slots['_awaiting'] ?? null;  // nama slot | konfirmasi | koreksi_pilih | null
        $data     = $this->dataOnly($slots);

        // 1. Minta staf
        if ($this->matches($lower, ['staf', 'staff', 'admin', 'operator', 'customer service', 'cs', 'petugas', 'manusia', 'panitia'])
            && (str_word_count($lower) <= 4 || $this->matches($lower, ['hubung', 'sambung', 'bicara', 'ngobrol', 'bantu', 'panggil', 'minta', 'kontak', 'mau', 'ingin', 'butuh', 'chat']))) {
            return $this->result('handoff', 'Baik, saya hubungkan Anda dengan staf kami. Mohon tunggu sebentar...', $slots);
        }

        // 2. Batal
        if ($mode === 'register' && $this->matches($lower, ['batal', 'batalkan', 'berhenti', 'cancel', 'tidak jadi'])) {
            return $this->result('cancel', 'Baik, proses pengisian formulir dihentikan. Data yang sudah terisi tetap ada di formulir. Ketik "daftar" kapan saja untuk melanjutkan.', $this->state($data, 'register', null));
        }

        // 3. Menunggu konfirmasi akhir
        if ($awaiting === 'konfirmasi') {
            if ($this->isYes($lower)) {
                return $this->result('registration_complete', "Terima kasih! 🎉 Seluruh data sudah saya isikan ke formulir.\n\nSilakan periksa kembali isian di formulir (lengkapi data tambahan bila ada), lalu klik tombol \"Simpan Pendaftaran\".", $this->state($data, 'done', 'selesai'));
            }
            if ($this->isNo($lower) || $this->matches($lower, ['koreksi', 'ubah', 'ganti', 'salah', 'revisi'])) {
                $target = $this->detectCorrectionSlot($lower);
                if ($target) {
                    return $this->askCorrection($target, $data);
                }
                return $this->result('correction', 'Baik, bagian mana yang ingin diubah? (misalnya: nama, alamat, tanggal lahir, nomor HP, dll.)', $this->state($data, 'register', 'koreksi_pilih'));
            }
            // Pengguna langsung menyebut koreksi beserta nilainya
            $entities = $this->extractEntities($text, null);
            if ($entities) {
                $data = array_merge($data, $entities);
                return $this->nextStep($data, 'Siap, data sudah diperbarui. ');
            }
            return $this->result('summary_confirmation', 'Mohon jawab "Ya" jika data sudah benar, atau "Tidak" jika ada yang ingin diubah.', $this->state($data, 'register', 'konfirmasi'));
        }

        // 4. Memilih bagian yang dikoreksi
        if ($awaiting === 'koreksi_pilih') {
            $target = $this->detectCorrectionSlot($lower);
            if ($target) {
                $entities = $this->extractEntities($text, null);
                if (isset($entities[$target])) {
                    $data = array_merge($data, $entities);
                    return $this->nextStep($data, 'Siap, data sudah diperbarui. ');
                }
                return $this->askCorrection($target, $data);
            }
            return $this->result('correction', 'Maaf, saya belum menangkap bagian yang dimaksud. Sebutkan salah satu: nama, jenis kelamin, tempat lahir, tanggal lahir, agama, alamat, kelas, asal sekolah, nama/pekerjaan ayah, nama/pekerjaan ibu, atau nomor HP.', $this->state($data, 'register', 'koreksi_pilih'));
        }

        // 5. Pertanyaan (FAQ) — dijawab tanpa mengganggu proses pengisian
        $slotAnswerExpected = $mode === 'register' && $awaiting && isset(self::SLOTS[$awaiting]);
        if ($this->isQuestion($lower, $text) && !$this->isRegisterIntent($lower)) {
            $faq = $this->answerFaq($lower);
            if ($faq !== null) {
                if ($slotAnswerExpected) {
                    $faq .= "\n\nSilakan lanjutkan pengisian formulir ya.\n" . self::SLOTS[$awaiting];
                }
                return $this->result('faq', $faq, $slots);
            }
        }

        // 6. Ekstraksi entitas dari pesan
        $entities = $this->extractEntities($text, $slotAnswerExpected ? $awaiting : null);

        // 7. Mulai / lanjut proses pendaftaran
        $wantsRegister = $this->isRegisterIntent($lower);
        if ($wantsRegister || $mode === 'register' || !empty($entities)) {
            if ($mode === 'done' && $wantsRegister) {
                $data = []; // mulai ulang dari awal
            }
            $data   = array_merge($data, $entities);
            $prefix = '';
            if ($mode !== 'register') {
                $prefix = $wantsRegister
                    ? "Siap! Saya bantu isi formulir pendaftaran ya — jawaban Anda akan langsung terisi otomatis di formulir. 📝\n\n"
                    : "Baik, data tersebut sudah saya isikan ke formulir. ";
            } elseif ($slotAnswerExpected && !isset($entities[$awaiting]) && empty($entities)) {
                // Jawaban tidak dapat dipahami untuk pertanyaan saat ini
                return $this->result('register', $this->invalidAnswer($awaiting), $this->state($data, 'register', $awaiting));
            } elseif (!empty($entities)) {
                $prefix = $this->ackMessage($entities);
            }
            return $this->nextStep($data, $prefix);
        }

        // 8. Sapaan
        if ($this->isGreeting($lower)) {
            return $this->result('greeting', "Halo! 👋 Saya asisten virtual pendaftaran SLAPUR.\n\nSaya bisa membantu:\n• Mengisi formulir pendaftaran secara otomatis (ketik \"daftar\")\n• Menjawab info biaya, syarat, jadwal, dan asrama\n• Menghubungkan Anda dengan staf", $slots);
        }

        // 9. FAQ tanpa tanda tanya (mis. "info biaya")
        $faq = $this->answerFaq($lower);
        if ($faq !== null) {
            return $this->result('faq', $faq, $slots);
        }

        return $this->result('out_of_scope', 'Maaf, saya belum memahami maksud Anda. Ketik "daftar" untuk mengisi formulir dibantu asisten, tanyakan info (biaya, syarat, jadwal, asrama), atau ketik "staf" untuk berbicara dengan petugas.', $slots);
    }

    // ------------------------------------------------------------ Slot flow

    protected function nextStep(array $data, string $prefix = ''): array
    {
        foreach (self::SLOTS as $key => $question) {
            if (!isset($data[$key]) || $data[$key] === '') {
                return $this->result('register', $prefix . $this->personalize($question, $data), $this->state($data, 'register', $key));
            }
        }

        $summary = $prefix . "Berikut rangkuman data yang sudah terisi di formulir:\n";
        foreach (self::LABELS as $key => $label) {
            $summary .= "• {$label}: " . $this->display($key, $data[$key]) . "\n";
        }
        $summary .= "\nApakah data ini sudah benar? (Ya / Tidak)";

        return $this->result('summary_confirmation', $summary, $this->state($data, 'register', 'konfirmasi'));
    }

    protected function askCorrection(string $slot, array $data): array
    {
        $label = strtolower(self::LABELS[$slot]);
        return $this->result('correction', "Baik, silakan kirim {$label} yang benar.\n" . self::SLOTS[$slot], $this->state($data, 'register', $slot));
    }

    protected function personalize(string $question, array $data): string
    {
        if (!empty($data['nama']) && !str_contains($question, 'nama lengkap')) {
            $first = explode(' ', $data['nama'])[0];
            return "Baik {$first}, " . lcfirst($question);
        }
        return $question;
    }

    protected function ackMessage(array $entities): string
    {
        $labels = [];
        foreach (array_keys($entities) as $key) {
            if (isset(self::LABELS[$key])) {
                $labels[] = strtolower(self::LABELS[$key]);
            }
        }
        return $labels ? 'Oke, ' . implode(', ', $labels) . " sudah dicatat ✔️\n\n" : '';
    }

    protected function invalidAnswer(string $slot): string
    {
        $hints = [
            'nama'           => 'Nama sebaiknya hanya berisi huruf.',
            'jenis_kelamin'  => 'Jawab "Laki-laki" atau "Perempuan".',
            'tempat_lahir'   => 'Sebutkan nama kota kelahiran.',
            'tanggal_lahir'  => 'Gunakan format seperti 12-08-2008 atau 12 Agustus 2008.',
            'agama'          => 'Pilihan: Islam, Kristen, Katolik, Hindu, Buddha, atau Konghucu.',
            'alamat'         => 'Tuliskan alamat selengkapnya.',
            'kelas'          => 'Pilih kelas 7, 8, 9, 10, 11, atau 12.',
            'asal_sekolah'   => 'Tuliskan nama sekolah asal, atau "-" jika belum ada.',
            'nama_ayah'      => 'Nama sebaiknya hanya berisi huruf.',
            'pekerjaan_ayah' => 'Tuliskan pekerjaan, misalnya "Petani" atau "Wiraswasta".',
            'nama_ibu'       => 'Nama sebaiknya hanya berisi huruf.',
            'pekerjaan_ibu'  => 'Tuliskan pekerjaan, misalnya "Ibu rumah tangga".',
            'telepon'        => 'Nomor harus diawali 08 atau 62 (10–14 digit).',
        ];
        return 'Maaf, jawaban tersebut belum bisa saya pahami. ' . ($hints[$slot] ?? '') . "\n" . self::SLOTS[$slot];
    }

    // --------------------------------------------------------- Entity extraction

    /**
     * @param string|null $awaiting Slot yang sedang ditanyakan (untuk menafsirkan jawaban singkat).
     */
    protected function extractEntities(string $text, ?string $awaiting): array
    {
        $e     = [];
        $t     = $text . ' ';
        $lower = mb_strtolower($t);

        // --- Telepon
        if (preg_match('/(?<!\d)(?:\+62|62|0)8[\d\s\-]{8,14}\d(?!\d)/', $t, $m)) {
            $num = preg_replace('/\D/', '', $m[0]);
            if (strlen($num) >= 10 && strlen($num) <= 14) {
                $e['telepon'] = $num;
                $t = str_replace($m[0], ' ', $t); // hindari tumpang tindih dengan tanggal
            }
        }

        // --- Tanggal lahir
        $date = $this->parseDate($t);
        if ($date) {
            $e['tanggal_lahir'] = $date;
        }

        // --- Jenis kelamin
        $freeText = $awaiting && in_array($awaiting, ['nama_ayah', 'nama_ibu', 'alamat', 'asal_sekolah', 'pekerjaan_ayah', 'pekerjaan_ibu', 'tempat_lahir'], true);
        if (!$freeText) {
            if (preg_match('/\b(laki[\s\-]?laki|cowok|pria)\b/u', $lower)) {
                $e['jenis_kelamin'] = 'Laki-laki';
            } elseif (preg_match('/\b(perempuan|cewek|wanita)\b/u', $lower)) {
                $e['jenis_kelamin'] = 'Perempuan';
            } elseif ($awaiting === 'jenis_kelamin') {
                if (preg_match('/^\s*(l|laki|cowo|putra)\s*$/u', $lower)) $e['jenis_kelamin'] = 'Laki-laki';
                if (preg_match('/^\s*(p|cewe|putri)\s*$/u', $lower)) $e['jenis_kelamin'] = 'Perempuan';
            }
        }

        // --- Agama (kata agama eksplisit dikenali kapan saja kecuali saat menjawab nama/pekerjaan/alamat)
        if ($awaiting === 'agama' || !$freeText || preg_match('/\bagama\b/u', $lower)) {
            if (preg_match('/\b(islam|kristen|protestan|katolik|katholik|hindu|buddha|budha|konghucu)\b/u', $lower, $m)) {
                $e['agama'] = match ($m[1]) {
                    'islam' => 'Islam',
                    'kristen', 'protestan' => 'Kristen',
                    'katolik', 'katholik' => 'Katolik',
                    'hindu' => 'Hindu',
                    'buddha', 'budha' => 'Buddha',
                    default => 'Konghucu',
                };
            }
        }

        // --- Kelas
        $romans = ['vii' => '7', 'viii' => '8', 'ix' => '9', 'x' => '10', 'xi' => '11', 'xii' => '12'];
        if (preg_match('/\bkelas\s*(12|11|10|9|8|7|xii|xi|ix|x|viii|vii)\b/u', $lower, $m)) {
            $e['kelas'] = $romans[$m[1]] ?? $m[1];
        } elseif ($awaiting === 'kelas' && preg_match('/^\s*(12|11|10|9|8|7|xii|xi|ix|x|viii|vii)\s*(?:smp|sma|smk)?\s*$/u', $lower, $m)) {
            $e['kelas'] = $romans[$m[1]] ?? $m[1];
        }

        // --- Nilai berlabel
        $labelled = [
            'nama' => '(?<![a-z])(?:nama(?!\s+(?:ayah|ibu|wali|orang|sekolah|bapak|bunda))(?:\s+lengkap)?(?:\s+(?:saya|aku|siswa|anak|calon\s+siswa))?|namaku|(?:saya|aku)\s+bernama)',
            'nama_ayah' => 'nama\s+(?:ayah|bapak|wali|orang\s*tua)(?:\s+(?:saya|aku|kandung))?|(?:ayah|bapak)(?:ku|\s+saya)?\s+(?:bernama|namanya)',
            'nama_ibu' => 'nama\s+(?:ibu|bunda|mama)(?:\s+(?:saya|aku|kandung))?|(?:ibu|bunda)(?:ku|\s+saya)?\s+(?:bernama|namanya)',
            'pekerjaan_ayah' => 'pekerjaan\s+(?:ayah|bapak|wali)(?:\s+saya)?|(?:ayah|bapak)(?:ku|\s+saya)?\s+(?:bekerja\s+sebagai|kerja\s+sebagai|bekerja\s+di|adalah\s+seorang|seorang)',
            'pekerjaan_ibu' => 'pekerjaan\s+(?:ibu|bunda|mama)(?:\s+saya)?|(?:ibu|bunda)(?:ku|\s+saya)?\s+(?:bekerja\s+sebagai|kerja\s+sebagai|bekerja\s+di|adalah\s+seorang|seorang)',
            'tempat_lahir' => 'tempat\s+lahir(?:\s+saya)?|lahir\s+di|kota\s+lahir',
            'asal_sekolah' => 'asal\s+sekolah(?:\s+saya)?|sekolah\s+asal|berasal\s+dari|pindahan\s+dari|lulusan',
            'agama' => 'agama(?:\s+saya)?',
        ];
        foreach ($labelled as $slot => $label) {
            $val = $this->grab($t, $label);
            if ($val !== null && $this->validateSlot($slot, $val)) {
                $e[$slot] = $this->normalizeSlot($slot, $val);
            }
        }
        // Agama berlabel yang bukan salah satu pilihan tidak dipakai
        if (isset($e['agama']) && !in_array($e['agama'], ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'], true)) {
            unset($e['agama']);
        }
        $addr = $this->grab($t, 'alamat(?:\s+(?:lengkap|saya|rumah|kami))?|(?:saya\s+)?(?:tinggal|bertempat\s+tinggal)\s+di', self::ADDRESS_TERM, 5);
        if ($addr !== null) {
            $e['alamat'] = $addr;
        }

        // "Bandung, 12-08-2008" ketika menjawab tempat/tanggal lahir
        if ($date && !isset($e['tempat_lahir'])) {
            if (preg_match('/^\s*(?:lahir\s+di\s+)?([\p{L}][\p{L}\s\.]{1,40}?)\s*,\s*\d/u', $t, $m) && $this->validateSlot('tempat_lahir', $m[1])) {
                $e['tempat_lahir'] = $this->normalizeSlot('tempat_lahir', $m[1]);
            }
        }

        // --- Jawaban polos untuk pertanyaan yang sedang ditanyakan
        if ($awaiting && empty($e) && !$this->isQuestion($lower, $text)) {
            $plain = $this->plainAnswer($awaiting, $text);
            if ($plain !== null) {
                $e[$awaiting] = $plain;
            }
        }

        return $e;
    }

    protected function plainAnswer(string $slot, string $text): ?string
    {
        $clean = trim($text, " \t\n\r.,;");
        // Buang awalan basa-basi
        $clean = trim(preg_replace('/^(?:oh|eh|ya|iya|oke|ok|baik|itu|yaitu|adalah|namanya|nama|atas\s+nama|beliau|sebagai|bekerja\s+sebagai|kerja\s+sebagai|dari|di)\s+/iu', '', $clean));
        if ($clean === '') {
            return null;
        }

        switch ($slot) {
            case 'nama':
            case 'nama_ayah':
            case 'nama_ibu':
            case 'tempat_lahir':
            case 'pekerjaan_ayah':
            case 'pekerjaan_ibu':
                return $this->validateSlot($slot, $clean) ? $this->normalizeSlot($slot, $clean) : null;
            case 'alamat':
                return mb_strlen($clean) >= 5 ? $clean : null;
            case 'asal_sekolah':
                if ($clean === '-' || preg_match('/^(tidak ada|belum ada|nggak ada|gak ada)$/iu', $clean)) {
                    return '-';
                }
                $clean = trim(preg_replace('/^(?:asal\s+sekolah|sekolah)\s+/iu', '', $clean));
                return mb_strlen($clean) >= 2 ? $clean : null;
        }
        return null;
    }

    protected function validateSlot(string $slot, string $val): bool
    {
        $val = trim($val);
        $len = mb_strlen($val);
        switch ($slot) {
            case 'nama':
            case 'nama_ayah':
            case 'nama_ibu':
                return $len >= 2 && $len <= 60 && preg_match('/^[\p{L}][\p{L}\s\.\'\-]*$/u', $val) === 1
                    && !preg_match('/\b(tidak|bukan|gak|nggak|apa|siapa)\b/iu', $val);
            case 'tempat_lahir':
                return $len >= 2 && $len <= 40 && preg_match('/^[\p{L}][\p{L}\s\.\-]*$/u', $val) === 1;
            case 'pekerjaan_ayah':
            case 'pekerjaan_ibu':
                return $len >= 3 && $len <= 40 && preg_match('/^[\p{L}][\p{L}\s\.\/\-,]*$/u', $val) === 1;
            case 'asal_sekolah':
                return $len >= 2 && $len <= 80;
            case 'agama':
                return $len >= 3 && $len <= 20;
        }
        return $len > 0;
    }

    protected function normalizeSlot(string $slot, string $val): string
    {
        $val = trim($val);
        return match ($slot) {
            'nama', 'nama_ayah', 'nama_ibu', 'tempat_lahir' => mb_convert_case($val, MB_CASE_TITLE, 'UTF-8'),
            'pekerjaan_ayah', 'pekerjaan_ibu' => mb_convert_case($val, MB_CASE_TITLE, 'UTF-8'),
            'agama' => $this->normalizeReligion($val),
            default => $val,
        };
    }

    protected function normalizeReligion(string $val): string
    {
        $map = ['islam' => 'Islam', 'kristen' => 'Kristen', 'protestan' => 'Kristen', 'katolik' => 'Katolik',
            'katholik' => 'Katolik', 'hindu' => 'Hindu', 'buddha' => 'Buddha', 'budha' => 'Buddha', 'konghucu' => 'Konghucu'];
        return $map[mb_strtolower($val)] ?? $val;
    }

    /** Ambil nilai setelah sebuah label (mis. "nama saya <nilai>"). */
    protected function grab(string $t, string $label, ?string $term = null, int $minLen = 2): ?string
    {
        $term = $term ?? self::TERM;
        $re   = '/(?:' . $label . ')\s*(?:adalah|yaitu|di|:|=)?\s*(.+?)(?=' . $term . ')/iu';
        if (preg_match($re, $t, $m)) {
            $val = trim($m[1], " \t\n\r,.;:");
            return mb_strlen($val) >= $minLen ? $val : null;
        }
        return null;
    }

    /** @return string|null tanggal ISO (YYYY-MM-DD) */
    protected function parseDate(string $t): ?string
    {
        $d = $m = $y = null;

        if (preg_match('/(?<!\d)(\d{4})-(\d{1,2})-(\d{1,2})(?!\d)/', $t, $x)) {
            [$y, $m, $d] = [(int) $x[1], (int) $x[2], (int) $x[3]];
        } elseif (preg_match('/(?<!\d)(\d{1,2})[\/\-\.\s](\d{1,2})[\/\-\.\s](\d{4})(?!\d)/', $t, $x)) {
            [$d, $m, $y] = [(int) $x[1], (int) $x[2], (int) $x[3]];
        } elseif (preg_match('/(?<!\d)(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})(?!\d)/u', $t, $x)) {
            $mon = self::MONTHS[mb_strtolower($x[2])] ?? null;
            if ($mon) {
                [$d, $m, $y] = [(int) $x[1], $mon, (int) $x[3]];
            }
        }

        if ($d && $m && $y && $y >= 1980 && $y <= (int) date('Y') && checkdate($m, $d, $y)) {
            return sprintf('%04d-%02d-%02d', $y, $m, $d);
        }
        return null;
    }

    // ------------------------------------------------------------ Intent helpers

    protected function isRegisterIntent(string $lower): bool
    {
        return $this->matches($lower, ['daftar', 'mendaftar', 'mendaftarkan', 'pendaftaran', 'registrasi', 'isi formulir', 'isi form', 'bantu isi', 'mulai isi'])
            && $this->matches($lower, ['mau', 'ingin', 'pingin', 'pengen', 'bantu', 'tolong', 'mulai', 'cara', 'gimana', 'bagaimana', 'isi', 'daftar', 'registrasi'])
            && !$this->matches($lower, ['biaya', 'syarat', 'jadwal', 'kapan', 'berapa']);
    }

    protected function isQuestion(string $lower, string $text): bool
    {
        if (str_contains($text, '?')) {
            return true;
        }
        return (bool) preg_match('/^(apa|apakah|berapa|bagaimana|gimana|kapan|dimana|di mana|kenapa|mengapa|siapa|bisa|boleh|ada)\b/u', $lower);
    }

    protected function isGreeting(string $lower): bool
    {
        return (bool) preg_match('/^(halo|hai|hi|hello|hallo|assalamualaikum|assalamu\'alaikum|selamat\s+(pagi|siang|sore|malam)|permisi|p|tes|test)\b[\s\!\.\,]*$/u', $lower)
            || (bool) preg_match('/^(halo|hai|hi|hello|assalamualaikum|selamat\s+(pagi|siang|sore|malam))\b/u', $lower);
    }

    protected function isYes(string $lower): bool
    {
        return (bool) preg_match('/^(ya|iya|yes|y|benar|betul|sudah\s+(benar|betul|sesuai|pas)|setuju|oke|ok|sip|lanjut|konfirmasi|sesuai|pas)\b/u', $lower)
            && !preg_match('/\b(tidak|belum|salah|ubah|ganti)\b/u', $lower);
    }

    protected function isNo(string $lower): bool
    {
        return (bool) preg_match('/^(tidak|tdk|enggak|nggak|gak|ga|no|belum|salah|bukan)\b/u', $lower);
    }

    protected function detectCorrectionSlot(string $lower): ?string
    {
        foreach (self::CORRECTION_KEYWORDS as $slot => $words) {
            foreach ($words as $w) {
                if (preg_match('/\b' . preg_quote($w, '/') . '\b/u', $lower)) {
                    return $slot;
                }
            }
        }
        return null;
    }

    protected function matches(string $lower, array $words): bool
    {
        foreach ($words as $w) {
            if (preg_match('/(?<![\p{L}])' . preg_quote($w, '/') . '/u', $lower)) {
                return true;
            }
        }
        return false;
    }

    // ----------------------------------------------------------------- FAQ

    protected function answerFaq(string $lower): ?string
    {
        // 1. Basis pengetahuan dari database (cocokkan kata kunci)
        try {
            $tokens = $this->tokens($lower);
            if ($tokens) {
                $best = null;
                $bestScore = 0;
                foreach (KnowledgeItem::all() as $item) {
                    $qTokens = $this->tokens(mb_strtolower((string) $item->question));
                    $score   = count(array_intersect($tokens, $qTokens));
                    if ($score > $bestScore) {
                        $best = $item;
                        $bestScore = $score;
                    }
                }
                if ($best && $bestScore >= min(2, count($tokens))) {
                    return (string) $best->answer;
                }
            }
        } catch (\Throwable $e) {
            // abaikan, lanjut ke jawaban bawaan
        }

        // 2. Biaya dari tabel fee
        if ($this->matches($lower, ['biaya', 'spp', 'uang', 'bayar', 'tarif', 'harga'])) {
            try {
                $fees = Fee::all();
                if ($fees->count()) {
                    $list = $fees->map(fn ($f) => "• {$f->name}: Rp" . number_format((float) $f->amount, 0, ',', '.'))->implode("\n");
                    return "Berikut rincian biaya:\n{$list}";
                }
            } catch (\Throwable $e) {
            }
            return 'Rincian biaya dapat dilihat pada menu Pembayaran setelah Anda menyelesaikan pendaftaran. Untuk informasi lebih lanjut, ketik "staf".';
        }

        // 3. Jawaban bawaan
        $builtin = [
            ['kw' => ['syarat', 'persyaratan', 'berkas', 'dokumen'], 'a' => 'Persyaratan umum: akte kelahiran, kartu keluarga, pas foto, dan rapor/ijazah sekolah asal. Berkas dapat diunggah setelah formulir disimpan.'],
            ['kw' => ['jadwal', 'kapan', 'dibuka', 'tanggal pendaftaran', 'periode'], 'a' => 'Jadwal pendaftaran dapat dilihat pada halaman utama portal. Untuk kepastian jadwal, silakan hubungi staf kami.'],
            ['kw' => ['asrama', 'kamar', 'fasilitas'], 'a' => 'Siswa tinggal di asrama dengan fasilitas kamar, kafetaria, dan pengawasan staf asrama.'],
            ['kw' => ['beasiswa'], 'a' => 'Informasi beasiswa dapat ditanyakan langsung kepada staf administrasi. Ketik "staf" untuk dihubungkan.'],
            ['kw' => ['status', 'diterima', 'pengumuman', 'hasil'], 'a' => 'Anda dapat melihat status pendaftaran pada menu "Status Pendaftaran" setelah masuk ke akun siswa.'],
            ['kw' => ['cara daftar', 'alur', 'langkah', 'prosedur'], 'a' => 'Alurnya: isi formulir (bisa dibantu saya — ketik "daftar"), unggah berkas, lalu lakukan pembayaran. Status dapat dipantau di portal.'],
            ['kw' => ['jenjang', 'smp', 'sma', 'kelas apa'], 'a' => 'Kami menerima pendaftaran jenjang SMP (kelas 7–9) dan SMA (kelas 10–12).'],
        ];
        foreach ($builtin as $item) {
            if ($this->matches($lower, $item['kw'])) {
                return $item['a'];
            }
        }
        return null;
    }

    protected function tokens(string $lower): array
    {
        $stop = ['yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'apa', 'ada', 'itu', 'ini', 'saya', 'aku', 'kak', 'min', 'dong', 'ya', 'nih', 'berapa', 'bagaimana', 'gimana', 'bisa', 'boleh', 'apakah', 'dengan', 'nya', 'sih', 'aja'];
        $words = preg_split('/[^\p{L}\p{N}]+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_filter($words, fn ($w) => mb_strlen($w) > 2 && !in_array($w, $stop, true))));
    }

    // ------------------------------------------------------------------ Util

    protected function display(string $key, string $value): string
    {
        if ($key === 'tanggal_lahir' && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
            $names = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return (int) $m[3] . ' ' . $names[(int) $m[2]] . ' ' . $m[1];
        }
        if ($key === 'kelas') {
            return 'Kelas ' . $value . ((int) $value <= 9 ? ' (SMP)' : ' (SMA)');
        }
        if ($key === 'asal_sekolah' && $value === '-') {
            return '-';
        }
        return $value;
    }

    protected function dataOnly(array $slots): array
    {
        return array_filter($slots, fn ($v, $k) => isset(self::SLOTS[$k]) && is_string($v) && $v !== '', ARRAY_FILTER_USE_BOTH);
    }

    protected function state(array $data, ?string $mode, ?string $awaiting): array
    {
        $state = $data;
        if ($mode) {
            $state['_mode'] = $mode;
        }
        if ($awaiting) {
            $state['_awaiting'] = $awaiting;
        }
        return $state;
    }

    protected function result(string $intent, string $reply, array $slots): array
    {
        return ['intent' => $intent, 'reply' => $reply, 'slots' => $slots];
    }
}
