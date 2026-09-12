<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotAnswer;
use App\Models\ChatbotConversation;
use App\Models\ChatbotSession;
use App\Models\MataPelajaranTingkat;
use App\Models\PendaftaranKamar;
use App\Models\PendaftaranPayment;
use App\Models\PendaftaranProfile;
use App\Models\PendaftaranRoomSelection;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class ChatbotController extends Controller
{
    private const STEP_PROFILE = 'profile';
    private const STEP_PAYMENT = 'payment';
    private const STEP_KAMAR = 'kamar';
    private const STEP_KURIKULUM = 'kurikulum';
    private const STEP_DOKUMEN = 'dokumen';

    private function ensureJsonInput(Request $request): void
    {
        $contentType = $request->header('Content-Type', '');
        if (str_starts_with($contentType, 'application/json') && $request->getContent()) {
            $data = json_decode($request->getContent(), true);
            if (is_array($data)) {
                $request->merge($data);
            }
        }
    }

    /**
     * POST /api/chatbot/message
     * Body: { "session_id": "...", "message": "..." }
     * Opsional: Authorization Bearer token agar alur pendaftaran tersimpan ke user.
     */
    public function message(Request $request): JsonResponse
    {
        $this->ensureJsonInput($request);

        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $message = trim($request->input('message'));
        $sessionId = $request->input('session_id') ?: Str::uuid()->toString();

        // Resolve user dari Bearer token (route chatbot tidak pakai auth:sanctum)
        $user = $request->user();
        if (! $user && $request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            $user = $token?->tokenable;
        }
        if ($user) {
            $request->setUserResolver(fn () => $user);
        }

        $session = ChatbotSession::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user?->id,
                'current_step_key' => null,
                'registration_data' => [],
                'last_activity_at' => now(),
            ]
        );

        if ($user) {
            $session->update(['user_id' => $user->id]);
        }
        $session->update(['last_activity_at' => now()]);

        ChatbotConversation::create([
            'session_id' => $sessionId,
            'role' => 'user',
            'message' => $message,
        ]);

        $reply = $this->getReply($session, $message);
        $answerId = $this->getLastMatchedAnswerId($session, $message);

        ChatbotConversation::create([
            'session_id' => $sessionId,
            'role' => 'assistant',
            'message' => $reply,
            'answer_id' => $answerId,
        ]);

        $stepFromRegistration = in_array($session->current_step_key, [self::STEP_PROFILE, self::STEP_PAYMENT, self::STEP_KAMAR, self::STEP_KURIKULUM, self::STEP_DOKUMEN], true);
        $nextStep = $this->getNextStep($session, $message);
        if (!$stepFromRegistration && $nextStep) {
            $session->update(['current_step_key' => $nextStep]);
        }

        return response()->json([
            'reply' => $reply,
            'session_id' => $sessionId,
            'step' => $session->fresh()->current_step_key,
        ]);
    }

    /**
     * GET /api/chatbot/history?session_id=xxx
     */
    public function history(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return response()->json(['messages' => []]);
        }

        $messages = ChatbotConversation::where('session_id', $sessionId)
            ->orderBy('id')
            ->get(['role', 'message', 'created_at'])
            ->map(fn ($m) => [
                'role' => $m->role,
                'message' => $m->message,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * POST /api/chatbot/clear — Hapus riwayat chat untuk session ini (tidak ada sampah chat).
     * Body: { "session_id": "..." }
     */
    public function clear(Request $request): JsonResponse
    {
        $this->ensureJsonInput($request);

        $sessionId = $request->input('session_id');

        if (!$sessionId) {
            return response()->json(['message' => 'session_id diperlukan'], 422);
        }

        $deleted = ChatbotConversation::where('session_id', $sessionId)->delete();

        $session = ChatbotSession::where('session_id', $sessionId)->first();
        if ($session) {
            $session->update([
                'current_step_key' => null,
                'registration_data' => [],
                'last_activity_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Chat berhasil dihapus. Anda bisa memulai percakapan baru.',
            'deleted_count' => $deleted,
        ]);
    }

    private function getReply(ChatbotSession $session, string $message): string
    {
        $currentStep = $session->current_step_key;
        $text = mb_strtolower(trim($message));
        $registrationData = $session->registration_data ?? [];
        $user = $session->user_id ? User::find($session->user_id) : null;

        if (in_array($currentStep, [self::STEP_PROFILE, self::STEP_PAYMENT, self::STEP_KAMAR, self::STEP_KURIKULUM, self::STEP_DOKUMEN], true)) {
            $reply = $this->handleRegistrationStep($session, $currentStep, $message, $text, $registrationData, $user);
            if ($reply !== null) {
                return $reply;
            }
        }

        if ($currentStep === 'verifikasi_email') {
            if ($this->isEmail($message)) {
                $session->update([
                    'current_step_key' => null,
                    'registration_data' => array_merge($session->registration_data ?? [], ['email_verified' => $message]),
                ]);
                return $this->verifikasiEmailReply($message);
            }
            return "Silakan ketik **email** yang Anda gunakan saat mendaftar (contoh: nama@email.com) untuk verifikasi data pendaftaran Anda.";
        }

        if (in_array($text, ['1', 'cara daftar', 'cara daftar?'], true) || str_contains($text, 'cara daftar')) {
            if (!$user) {
                return "Untuk mengisi **biodata pendaftaran**, silakan **login** terlebih dahulu di akun siswa, lalu kembali ke sini dan ketik **1** atau **cara daftar**.";
            }
            $nextStep = $this->getNextPendingStep($user);
            if ($nextStep === self::STEP_PROFILE) {
                $session->update(['current_step_key' => self::STEP_PROFILE, 'registration_data' => []]);
                return "**Langkah 1: Biodata**\n\nKetik **nama lengkap** Anda.";
            }
            if ($nextStep === self::STEP_PAYMENT) {
                $session->update(['current_step_key' => self::STEP_PAYMENT, 'registration_data' => []]);
                $total = (float) config('pendaftaran.total_tagihan', 5_000_000);
                $nominal60 = round($total * 0.6, 0);
                PendaftaranPayment::firstOrCreate(
                    ['user_id' => $user->id],
                    ['total_tagihan' => $total, 'nominal_60_percent' => $nominal60, 'nominal_dibayar' => 0, 'status' => 'menunggu']
                );
                return "**Langkah 2: Pembayaran**\n\n" . $this->buildPaymentMessage($user) . "\n\nSetelah transfer, ketik **sudah bayar** untuk verifikasi.";
            }
            if ($nextStep === self::STEP_KAMAR) {
                $session->update(['current_step_key' => self::STEP_KAMAR]);
                return $this->buildKamarListMessage();
            }
            if ($nextStep === self::STEP_KURIKULUM) {
                $session->update(['current_step_key' => self::STEP_KURIKULUM]);
                return $this->buildKurikulumMessage($user);
            }
            if ($nextStep === self::STEP_DOKUMEN) {
                $session->update(['current_step_key' => self::STEP_DOKUMEN]);
                return $this->buildDokumenMessage($user);
            }
            return "Pendaftaran Anda sudah lengkap. Cek **Dashboard** (Biodata, Clearance Slip, Pilih Kamar, Administrasi > Upload Dokumen). Ada yang bisa dibantu lagi? Ketik **bantuan**.";
        }

        $answer = $this->matchAnswer($session, $text);
        if ($answer) {
            $this->setLastMatchedAnswerId($answer->id);
            return $answer->response;
        }

        $default = ChatbotAnswer::where('topic', 'umum')->where('step_key', 'default')->first();
        $this->setLastMatchedAnswerId($default?->id);

        return $default?->response ?? 'Maaf, saya belum paham. Ketik **bantuan** untuk melihat opsi.';
    }

    private function handleRegistrationStep(ChatbotSession $session, string $currentStep, string $message, string $text, array $registrationData, ?User $user): ?string
    {
        if (!$user) {
            $session->update(['current_step_key' => null]);
            return "Sesi pendaftaran membutuhkan login. Silakan login lalu ketik **1** atau **cara daftar** lagi.";
        }

        if ($currentStep === self::STEP_PROFILE) {
            if (!isset($registrationData['nama_lengkap'])) {
                $session->update(['registration_data' => array_merge($registrationData, ['nama_lengkap' => $message])]);
                return "Ketik **no HP** Anda (contoh: 08123456789).";
            }
            if (!isset($registrationData['no_hp'])) {
                $session->update(['registration_data' => array_merge($session->registration_data ?? [], ['no_hp' => $message])]);
                return "Ketik **alamat** lengkap Anda.";
            }
            if (!isset($registrationData['alamat'])) {
                $session->update(['registration_data' => array_merge($session->registration_data ?? [], ['alamat' => $message])]);
                return "Ketik **kelas** yang ingin didaftar: **7**, **8**, **9** (SMP) atau **10**, **11**, **12** (SMA).";
            }
            if (!isset($registrationData['kelas_yang_didaftar'])) {
                $kelas = (int) preg_replace('/\D/', '', $message);
                if (!in_array($kelas, [7, 8, 9, 10, 11, 12], true)) {
                    return "Kelas harus 7–12. Ketik angka saja (contoh: 10).";
                }
                $session->update(['registration_data' => array_merge($session->registration_data ?? [], ['kelas_yang_didaftar' => $kelas])]);
                $data = $session->fresh()->registration_data ?? [];
                PendaftaranProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nama_lengkap' => $data['nama_lengkap'] ?? $user->name,
                        'no_hp' => $data['no_hp'] ?? null,
                        'alamat' => $data['alamat'] ?? null,
                        'kelas_yang_didaftar' => $data['kelas_yang_didaftar'],
                    ]
                );
                $session->update(['current_step_key' => self::STEP_PAYMENT, 'registration_data' => []]);
                return "**Biodata tersimpan.** Data tampil di menu **Dashboard > Biodata**.\n\n**Langkah 2: Pembayaran**\n\n" . $this->buildPaymentMessage($user);
            }
        }

        if ($currentStep === self::STEP_PAYMENT) {
            $payment = PendaftaranPayment::where('user_id', $user->id)->first();
            $hasBukti = $payment && $payment->bukti_path;

            if (str_contains($text, 'sudah bayar') || $text === 'sudah bayar' || $text === 'sudah') {
                $total = (float) config('pendaftaran.total_tagihan', 5_000_000);
                $nominal60 = round($total * 0.6, 0);
                PendaftaranPayment::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'total_tagihan' => $total,
                        'nominal_60_percent' => $nominal60,
                        'nominal_dibayar' => $nominal60,
                        'status' => 'terverifikasi',
                        'verified_at' => now(),
                    ]
                );
                $session->update(['current_step_key' => self::STEP_KAMAR]);
                return "**Pembayaran tercatat dan status Terverifikasi.** Cek di **Clearance Slip > Pembayaran Pendaftaran**.\n\n**Langkah 3: Pilih Kamar**\n\n" . $this->buildKamarListMessage();
            }

            if (str_contains($text, 'upload') || str_contains($text, 'bukti') || str_contains($text, 'sudah upload')) {
                if ($hasBukti) {
                    $session->update(['current_step_key' => self::STEP_KAMAR]);
                    return "**Bukti pembayaran Anda sudah diterima.** Status: menunggu verifikasi admin. Setelah diverifikasi, status akan **Terverifikasi** di **Clearance Slip > Pembayaran Pendaftaran**.\n\n**Langkah 3: Pilih Kamar**\n\n" . $this->buildKamarListMessage();
                }
                return "Gunakan tombol **Upload bukti** di bawah chat untuk mengirim foto bukti transfer. Setelah upload, ketik **sudah upload**.";
            }

            return $this->buildPaymentMessage($user) . "\n\n**Pilihan:**\n• Ketik **sudah bayar** jika sudah transfer (status langsung Terverifikasi).\n• Atau **upload bukti** pembayaran (foto transfer) dengan tombol di bawah, lalu ketik **sudah upload** (menunggu verifikasi admin).";
        }

        if ($currentStep === self::STEP_KAMAR) {
            $nomorKamar = trim(preg_replace('/\s+/', '', $message));
            $kamar = PendaftaranKamar::where('nomor_kamar', $nomorKamar)->first();
            if (!$kamar) {
                return "Kamar **{$nomorKamar}** tidak ditemukan. " . $this->buildKamarListMessage();
            }
            if ($kamar->current_occupancy >= $kamar->kapasitas) {
                return "Kamar **{$kamar->nomor_kamar}** sudah penuh (4 orang). Pilih nomor kamar lain.\n\n" . $this->buildKamarListMessage();
            }
            $existing = PendaftaranRoomSelection::where('user_id', $user->id)->first();
            if ($existing && $existing->pendaftaran_kamar_id === $kamar->id) {
                $session->update(['current_step_key' => self::STEP_KURIKULUM]);
                $profile = PendaftaranProfile::where('user_id', $user->id)->first();
                return "Anda sudah memilih kamar **{$kamar->nomor_kamar}**.\n\n**Langkah 4: Kurikulum**\n\n" . $this->buildKurikulumMessage($user, $profile?->kelas_yang_didaftar);
            }
            if ($existing && $existing->pendaftaran_kamar_id !== $kamar->id) {
                PendaftaranKamar::where('id', $existing->pendaftaran_kamar_id)->decrement('current_occupancy');
            }
            PendaftaranRoomSelection::updateOrCreate(
                ['user_id' => $user->id],
                ['pendaftaran_kamar_id' => $kamar->id]
            );
            $kamar->increment('current_occupancy');
            $session->update(['current_step_key' => self::STEP_KURIKULUM]);
            $profile = PendaftaranProfile::where('user_id', $user->id)->first();
            $tingkat = $profile?->kelas_yang_didaftar;
            return "**Kamar {$kamar->nomor_kamar}** terpilih.\n\n**Langkah 4: Kurikulum**\n\n" . $this->buildKurikulumMessage($user, $tingkat);
        }

        if ($currentStep === self::STEP_KURIKULUM) {
            $session->update(['current_step_key' => self::STEP_DOKUMEN]);
            return $this->buildDokumenMessage($user);
        }

        if ($currentStep === self::STEP_DOKUMEN) {
            $jenisList = [StudentDocument::JENIS_AKTE, StudentDocument::JENIS_KK, StudentDocument::JENIS_PAS_FOTO, StudentDocument::JENIS_RAPORT];
            foreach ($jenisList as $jenis) {
                StudentDocument::firstOrCreate(
                    ['user_id' => $user->id, 'jenis' => $jenis],
                    ['status' => 'pending']
                );
            }
            $session->update(['current_step_key' => null]);
            return "**Langkah 5: Upload Dokumen**\n\nMenu ini hanya untuk **mengupload & menyimpan** dokumen pendaftaran. Verifikasi dokumen akan dilakukan **nanti oleh staff**.\n\nDokumen yang diupload:\n• Akte Kelahiran\n• KK\n• Pas foto\n• Raport (bagi pindahan)\n\n**Pendaftaran via chatbot selesai.** Anda bisa lanjut upload dokumen kapan saja.";
        }

        return null;
    }

    private function getNextPendingStep(User $user): ?string
    {
        if (!PendaftaranProfile::where('user_id', $user->id)->exists()) {
            return self::STEP_PROFILE;
        }
        if (!PendaftaranPayment::where('user_id', $user->id)->exists()) {
            return self::STEP_PAYMENT;
        }
        if (!PendaftaranRoomSelection::where('user_id', $user->id)->exists()) {
            return self::STEP_KAMAR;
        }
        $docs = StudentDocument::where('user_id', $user->id)->count();
        if ($docs < 4) {
            return self::STEP_DOKUMEN;
        }
        return null;
    }

    private function buildPaymentMessage(User $user): string
    {
        $total = (float) config('pendaftaran.total_tagihan', 5_000_000);
        $nominal60 = round($total * 0.6, 0);
        $rekening = config('pendaftaran.rekening_verifikasi', 'Bank XXX - 1234567890 a.n. SLAPUR');
        $payment = PendaftaranPayment::where('user_id', $user->id)->first();
        if ($payment) {
            $status = $payment->status === 'terverifikasi' ? 'Terverifikasi' : ($payment->bukti_path ? 'Menunggu verifikasi (bukti sudah diupload)' : 'Menunggu');
            return "Total: **Rp " . number_format($payment->total_tagihan, 0, ',', '.') . "**. Bayar 60%: **Rp " . number_format($payment->nominal_60_percent, 0, ',', '.') . "**.\nRekening: **{$rekening}**\nStatus: {$status}.";
        }
        return "Total biaya pendaftaran: **Rp " . number_format($total, 0, ',', '.') . "**. Bayar **60%** = **Rp " . number_format($nominal60, 0, ',', '.') . "**.\nTransfer ke: **{$rekening}**.";
    }

    private function buildKamarListMessage(): string
    {
        $kamar = PendaftaranKamar::orderBy('nomor_kamar')->get();
        $lines = [];
        foreach ($kamar as $k) {
            $tersedia = $k->current_occupancy < $k->kapasitas;
            $lines[] = "• **{$k->nomor_kamar}** — " . ($tersedia ? "tersedia ({$k->current_occupancy}/{$k->kapasitas})" : "penuh");
        }
        return "Kamar tersedia:\n" . implode("\n", $lines) . "\n\nKetik **nomor kamar** yang dipilih (contoh: A1).";
    }

    private function buildKurikulumMessage(?User $user, ?int $tingkat = null): string
    {
        if ($tingkat === null && $user) {
            $profile = PendaftaranProfile::where('user_id', $user->id)->first();
            $tingkat = $profile?->kelas_yang_didaftar;
        }
        if ($tingkat === null) {
            return "Mata pelajaran per tingkat bisa dilihat di menu **Dashboard**. Lanjut ke **Upload Dokumen**.";
        }
        $items = MataPelajaranTingkat::where('tingkat', $tingkat)->orderBy('urutan')->pluck('nama_pelajaran');
        if ($items->isEmpty()) {
            return "Belum ada data mata pelajaran untuk kelas **{$tingkat}**. Lanjut ke **Upload Dokumen**.";
        }
        $list = $items->map(fn ($n) => "• {$n}")->implode("\n");
        return "**Mata pelajaran kelas {$tingkat}:**\n{$list}\n\nLanjut ke **Langkah 5: Upload Dokumen**. Ketik **lanjut** atau pesan apa saja.";
    }

    private function buildDokumenMessage(?User $user): string
    {
        return "**Langkah 5: Upload Dokumen**\n\nMenu ini hanya untuk **mengupload & menyimpan** dokumen pendaftaran. Verifikasi dokumen akan dilakukan **nanti oleh staff**.\n\nDokumen yang diupload:\n• Akte Kelahiran\n• KK\n• Pas foto\n• Raport (bagi pindahan)";
    }

    private function matchAnswer(ChatbotSession $session, string $text): ?ChatbotAnswer
    {
        $currentStep = $session->current_step_key;

        if ($currentStep === null) {
            $answers = ChatbotAnswer::where('topic', 'pendaftaran')
                ->where('step_key', '!=', 'verifikasi_email')
                ->orderBy('order')
                ->get();
        } else {
            $answers = ChatbotAnswer::where('topic', 'pendaftaran')
                ->where(function ($q) use ($currentStep) {
                    $q->whereNull('step_key')->orWhere('step_key', $currentStep);
                })
                ->orderBy('order')
                ->get();
        }

        foreach ($answers as $answer) {
            $keywords = $answer->trigger_keywords ?? [];
            if (empty($keywords) && $answer->step_key === 'verifikasi_email') {
                continue;
            }
            foreach ($keywords as $keyword) {
                if (str_contains($text, mb_strtolower($keyword))) {
                    return $answer;
                }
            }
        }

        return null;
    }

    private function isEmail(string $value): bool
    {
        return (bool) filter_var(trim($value), FILTER_VALIDATE_EMAIL);
    }

    private function verifikasiEmailReply(string $email): string
    {
        $user = User::where('email', trim($email))->first();
        if ($user) {
            $nama = $user->name ?? 'Siswa';
            return "**Verifikasi berhasil.**\n\nData terdaftar atas nama **{$nama}** (email: {$email}). Data pendaftaran Anda tercatat di sistem SLAPUR. Untuk kelengkapan dokumen dan status selanjutnya, silakan cek menu **Status Pendaftaran** atau hubungi Tata Usaha sekolah.\n\nAda yang bisa saya bantu lagi? Ketik **bantuan** untuk opsi.";
        }
        return "Email **{$email}** belum terdaftar di sistem SLAPUR. Silakan daftar terlebih dahulu melalui menu **Buat Akun** atau halaman pendaftaran, lalu coba verifikasi lagi dengan email yang sama.";
    }

    private ?int $lastMatchedAnswerId = null;

    private function setLastMatchedAnswerId(?int $id): void
    {
        $this->lastMatchedAnswerId = $id;
    }

    private function getLastMatchedAnswerId(ChatbotSession $session, string $message): ?int
    {
        return $this->lastMatchedAnswerId;
    }

    private function getNextStep(ChatbotSession $session, string $message): ?string
    {
        $text = mb_strtolower(trim($message));
        $answer = $this->matchAnswer($session, $text);
        return $answer?->next_step_key;
    }
}
