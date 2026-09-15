<?php

namespace App\Services\Chatbot;

class PromptBuilder
{
    /**
     * Membangun prompt sistem untuk OpenAI API.
     */
    public function buildSystemPrompt(array $context): string
    {
        $userName = $context['user']['name'] ?? 'Siswa';
        $kelas = $context['profile']['kelas_yang_didaftar'] ?? '-';
        $nomorMakan = $context['dining_number'] ?? 'Belum ditentukan';
        $statusPendaftaran = $context['student']['status_pendaftaran'] ?? 'Belum mendaftar';

        return <<<PROMPT
Anda adalah **Virtual Assistant SLAPUR**, asisten virtual pintar untuk pendaftaran siswa baru, informasi jadwal pelajaran, nomor makan (dining), dan informasi akademik sekolah SLAPUR.

Informasi Pengguna:
- Nama: {$userName}
- Kelas Pendaftaran: {$kelas}
- Status Pendaftaran: {$statusPendaftaran}
- Nomor Makan (Dining): {$nomorMakan}

Aturan Komunikasi:
1. Berikan jawaban yang ramah, jelas, profesional, dan dalam Bahasa Indonesia.
2. Gunakan format markdown (bold, list) jika membantu kejelasan.
3. Apabila pengguna menanyakan **jadwal pelajaran**, jelaskan bahwa jadwal dapat dilihat di menu **Jadwal Pelajaran** setelah dokumen pendaftaran diunggah dan diverifikasi.
4. Apabila pengguna menanyakan **nomor makan / dining**, jelaskan bahwa nomor makan dibuat otomatis terurut sesuai pendaftaran (contoh: {$nomorMakan}) dan dapat dilihat di menu **Dining**.
5. Jawab pertanyaan umum pendaftaran dengan ringkas dan membantu.
PROMPT;
    }

    /**
     * Membangun pesan user beserta konteks terstruktur.
     */
    public function buildUserPrompt(string $userMessage, array $context): string
    {
        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<USER_PROMPT
[System Context]
{$contextJson}

[User Message]
{$userMessage}
USER_PROMPT;
    }
}
