<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotSession;
use App\Models\Student;
use App\Models\User;

class ResponseProcessor
{
    /**
     * Post-process balasan akhir, menyelaraskan status pendaftaran, dokumen, jadwal, & dining.
     */
    public function process(string $reply, array $intentData, array $context, ChatbotSession $session, ?User $user = null): string
    {
        $processed = $reply;

        // 1. Apabila intent adalah upload dokumen dan pendaftaran lengkap -> beri tahu akses Jadwal Pelajaran & Nomor Makan
        if ($user && ($intentData['intent'] === IntentDetector::INTENT_DOCUMENT_UPLOAD || $session->current_step_key === 'dokumen')) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student && $student->has_uploaded_docs) {
                $nomorMakan = $student->getDiningNumberFormatted();
                $processed .= "\n\n🎉 **Selamat! Dokumen Pendaftaran Anda Telah Berhasil Diunggah.**\n";
                $processed .= "• **Jadwal Pelajaran**: Anda kini sudah dapat melihat jadwal pelajaran yang telah ditentukan di menu **Jadwal Pelajaran**.\n";
                $processed .= "• **Nomor Makan (Dining)**: Nomor urutan makan Anda adalah **{$nomorMakan}** (sesuai nomor pendaftaran). Silakan cek menu **Dining** di navbar.";
            }
        }

        return trim($processed);
    }
}
