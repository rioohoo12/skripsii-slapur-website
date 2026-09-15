<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotAnswer;
use App\Models\ChatbotSession;
use App\Models\MataPelajaranTingkat;
use App\Models\PendaftaranKamar;
use App\Models\PendaftaranPayment;
use App\Models\PendaftaranProfile;
use App\Models\PendaftaranRoomSelection;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;

class ChatbotService
{
    private const STEP_PROFILE   = 'profile';
    private const STEP_PAYMENT   = 'payment';
    private const STEP_KAMAR     = 'kamar';
    private const STEP_KURIKULUM = 'kurikulum';
    private const STEP_DOKUMEN   = 'dokumen';

    public function __construct(
        private ChatSessionService $sessionService,
        private ChatMessageService $messageService,
        private Preprocessor $preprocessor,
        private IntentDetector $intentDetector,
        private ContextBuilder $contextBuilder,
        private PromptBuilder $promptBuilder,
        private OpenAIService $openAIService,
        private ResponseProcessor $responseProcessor,
    ) {}

    /**
     * Memproses pesan masuk melalui seluruh pipeline Chatbot Core Architecture.
     */
    public function handleMessage(string $rawMessage, ?string $sessionId = null, ?User $user = null): array
    {
        // 1. Dapatkan / buat sesi percakapan (ChatSessionService)
        $session = $this->sessionService->getOrCreateSession($sessionId, $user);

        // 2. Preprocessing pesan (Preprocessor)
        $cleanMessage = $this->preprocessor->process($rawMessage);
        $normalizedLower = $this->preprocessor->toNormalizedLower($rawMessage);

        // 3. Catat pesan user ke basis data (ChatMessageService)
        $this->messageService->recordUserMessage($session->session_id, $cleanMessage);

        // 4. Deteksi Intent (IntentDetector)
        $intentData = $this->intentDetector->detect($normalizedLower, $session);

        // 5. Bangun Konteks Pengguna (ContextBuilder)
        $context = $this->contextBuilder->build($session, $user);

        // 6. Hasilkan Balasan (OpenAIService atau Rule Engine Fallback)
        $rawReply = null;
        $answerId = null;

        // Coba hasilkan via OpenAI jika terkonfigurasi dan bukan dalam step pendaftaran khusus
        if ($this->openAIService->isConfigured() && $intentData['intent'] === IntentDetector::INTENT_UNKNOWN) {
            $systemPrompt = $this->promptBuilder->buildSystemPrompt($context);
            $userPrompt = $this->promptBuilder->buildUserPrompt($cleanMessage, $context);
            $rawReply = $this->openAIService->generateCompletion($systemPrompt, $userPrompt);
        }

        // Jika OpenAI tidak terkonfigurasi atau mengembalikan null, gunakan Rule-based Data Engine
        if (!$rawReply) {
            $ruleResult = $this->executeRuleEngine($session, $cleanMessage, $normalizedLower, $intentData, $user, $context);
            $rawReply = $ruleResult['reply'];
            $answerId = $ruleResult['answer_id'];
        }

        // 7. Post-Processing Balasan (ResponseProcessor)
        $finalReply = $this->responseProcessor->process($rawReply, $intentData, $context, $session, $user);

        // 8. Catat balasan assistant ke basis data (ChatMessageService)
        $this->messageService->recordAssistantResponse($session->session_id, $finalReply, $answerId);

        return [
            'reply' => $finalReply,
            'session_id' => $session->session_id,
            'step' => $session->fresh()->current_step_key,
        ];
    }

    /**
     * Engine aturan (DataService rule fallback).
     */
    private function executeRuleEngine(ChatbotSession $session, string $cleanMessage, string $normalizedLower, array $intentData, ?User $user, array $context): array
    {
        $currentStep = $session->current_step_key;
        $registrationData = $session->registration_data ?? [];

        // Handler Step Pendaftaran Aktif
        if (in_array($currentStep, [self::STEP_PROFILE, self::STEP_PAYMENT, self::STEP_KAMAR, self::STEP_KURIKULUM, self::STEP_DOKUMEN], true)) {
            $reply = $this->handleRegistrationStep($session, $currentStep, $cleanMessage, $normalizedLower, $registrationData, $user);
            if ($reply !== null) {
                return ['reply' => $reply, 'answer_id' => null];
            }
        }

        // Handler Pendaftaran Awal (Intent REGISTRATION_START)
        if ($intentData['intent'] === IntentDetector::INTENT_REGISTRATION_START) {
            if (!$user) {
                return [
                    'reply' => "Untuk mengisi **biodata pendaftaran**, silakan **login** terlebih dahulu di akun siswa, lalu kembali ke sini dan ketik **1** atau **cara daftar**.",
                    'answer_id' => null,
                ];
            }
            $nextStep = $this->getNextPendingStep($user);
            if ($nextStep === self::STEP_PROFILE) {
                $this->sessionService->updateStep($session, self::STEP_PROFILE);
                return ['reply' => "**Langkah 1: Biodata**\n\nKetik **nama lengkap** Anda.", 'answer_id' => null];
            }
            if ($nextStep === self::STEP_PAYMENT) {
                $this->sessionService->updateStep($session, self::STEP_PAYMENT);
                $total = (float) config('pendaftaran.total_tagihan', 5_000_000);
                $nominal60 = round($total * 0.6, 0);
                PendaftaranPayment::firstOrCreate(
                    ['user_id' => $user->id],
                    ['total_tagihan' => $total, 'nominal_60_percent' => $nominal60, 'nominal_dibayar' => 0, 'status' => 'menunggu']
                );
                return ['reply' => "**Langkah 2: Pembayaran**\n\n" . $this->buildPaymentMessage($user) . "\n\nSetelah transfer, ketik **sudah bayar** untuk verifikasi.", 'answer_id' => null];
            }
            if ($nextStep === self::STEP_KAMAR) {
                $this->sessionService->updateStep($session, self::STEP_KAMAR);
                return ['reply' => $this->buildKamarListMessage(), 'answer_id' => null];
            }
            if ($nextStep === self::STEP_KURIKULUM) {
                $this->sessionService->updateStep($session, self::STEP_KURIKULUM);
                return ['reply' => $this->buildKurikulumMessage($user), 'answer_id' => null];
            }
            if ($nextStep === self::STEP_DOKUMEN) {
                $this->sessionService->updateStep($session, self::STEP_DOKUMEN);
                return ['reply' => $this->buildDokumenMessage($user), 'answer_id' => null];
            }
            return [
                'reply' => "Pendaftaran Anda sudah lengkap. Cek **Dashboard** (Biodata, Clearance Slip, Pilih Kamar, Administrasi > Upload Dokumen). Ada yang bisa dibantu lagi? Ketik **bantuan**.",
                'answer_id' => null,
            ];
        }

        // Handler Jadwal Pelajaran (Intent SCHEDULE_INQUIRY)
        if ($intentData['intent'] === IntentDetector::INTENT_SCHEDULE_INQUIRY) {
            $reply = "**Jadwal Pelajaran SLAPUR**\n\n";
            if ($user) {
                $student = Student::where('user_id', $user->id)->first();
                $profile = PendaftaranProfile::where('user_id', $user->id)->first();
                $kelas = $profile?->kelas_yang_didaftar ?? 'SMP/SMA';
                if ($student && $student->has_uploaded_docs) {
                    $reply .= "Dokumen pendaftaran Anda telah lengkap! Jadwal pelajaran untuk kelas **{$kelas}** telah ditentukan. Silakan buka menu **Jadwal Pelajaran** di sidebar untuk melihat jadwal mingguan Anda secara detail.";
                } else {
                    $reply .= "Jadwal pelajaran kelas **{$kelas}** akan langsung dibuka dan ditentukan setelah Anda menyelesaikan unggah dokumen pendaftaran. Silakan selesaikan upload dokumen terlebih dahulu.";
                }
            } else {
                $reply .= "Jadwal pelajaran disesuaikan dengan tingkat kelas (7-9 SMP & 10-12 SMA). Silakan login untuk melihat jadwal kelas Anda.";
            }
            return ['reply' => $reply, 'answer_id' => null];
        }

        // Handler Dining / Nomor Makan (Intent DINING_INQUIRY)
        if ($intentData['intent'] === IntentDetector::INTENT_DINING_INQUIRY) {
            $reply = "**Fasilitas Dining & Nomor Makan**\n\n";
            if ($user) {
                $student = Student::where('user_id', $user->id)->first();
                $nomorMakan = $student ? $student->getDiningNumberFormatted() : '001';
                $reply .= "Nomor makan urutan Anda di kantin adalah **{$nomorMakan}** (dibuat otomatis sesuai urutan pendaftaran Anda).\n";
                $reply .= "Anda dapat melihat detail jadwal makan dan menu harian di menu **Dining** pada sidebar navigation.";
            } else {
                $reply .= "Nomor makan dibuat otomatis mengikuti nomor urutan pendaftaran (contoh: 001, 002). Silakan login ke akun murid Anda untuk melihat nomor makan Anda.";
            }
            return ['reply' => $reply, 'answer_id' => null];
        }

        // Handler FAQ Match (Intent FAQ_MATCH)
        if ($intentData['intent'] === IntentDetector::INTENT_FAQ_MATCH && $intentData['answer']) {
            /** @var ChatbotAnswer $answer */
            $answer = $intentData['answer'];
            if ($answer->next_step_key) {
                $this->sessionService->updateStep($session, $answer->next_step_key);
            }
            return ['reply' => $answer->response, 'answer_id' => $answer->id];
        }

        // Default Fallback
        $default = ChatbotAnswer::where('topic', 'umum')->where('step_key', 'default')->first();
        return [
            'reply' => $default?->response ?? 'Maaf, saya belum memahami pesan Anda. Ketik **bantuan** untuk melihat daftar menu atau layanan.',
            'answer_id' => $default?->id,
        ];
    }

    private function handleRegistrationStep(ChatbotSession $session, string $currentStep, string $cleanMessage, string $normalizedLower, array $registrationData, ?User $user): ?string
    {
        if (!$user) {
            $this->sessionService->updateStep($session, null);
            return "Sesi pendaftaran membutuhkan login. Silakan login lalu ketik **1** atau **cara daftar** lagi.";
        }

        if ($currentStep === self::STEP_PROFILE) {
            if (!isset($registrationData['nama_lengkap'])) {
                $this->sessionService->updateRegistrationData($session, ['nama_lengkap' => $cleanMessage]);
                return "Ketik **no HP** Anda (contoh: 08123456789).";
            }
            if (!isset($registrationData['no_hp'])) {
                $this->sessionService->updateRegistrationData($session, ['no_hp' => $cleanMessage]);
                return "Ketik **alamat** lengkap Anda.";
            }
            if (!isset($registrationData['alamat'])) {
                $this->sessionService->updateRegistrationData($session, ['alamat' => $cleanMessage]);
                return "Ketik **kelas** yang ingin didaftar: **7**, **8**, **9** (SMP) atau **10**, **11**, **12** (SMA).";
            }
            if (!isset($registrationData['kelas_yang_didaftar'])) {
                $kelas = (int) preg_replace('/\D/', '', $cleanMessage);
                if (!in_array($kelas, [7, 8, 9, 10, 11, 12], true)) {
                    return "Kelas harus 7–12. Ketik angka saja (contoh: 10).";
                }
                $this->sessionService->updateRegistrationData($session, ['kelas_yang_didaftar' => $kelas]);
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

                // Auto create student record & dining number if not present
                $student = Student::where('user_id', $user->id)->first();
                if (!$student) {
                    $prefix = 'REG-' . date('Ym') . '-';
                    $lastStudent = Student::where('nomor_pendaftaran', 'LIKE', $prefix . '%')->orderBy('nomor_pendaftaran', 'desc')->first();
                    $newNum = $lastStudent && $lastStudent->nomor_pendaftaran ? ((int) str_replace($prefix, '', $lastStudent->nomor_pendaftaran)) + 1 : 1;
                    $regNum = $prefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);
                    $diningNum = str_pad($newNum, 3, '0', STR_PAD_LEFT);

                    Student::create([
                        'user_id' => $user->id,
                        'full_name' => $data['nama_lengkap'] ?? $user->name,
                        'gender' => $user->jenis_kelamin === 'perempuan' ? 'P' : 'L',
                        'nomor_pendaftaran' => $regNum,
                        'dining_number' => $diningNum,
                        'status_pendaftaran' => 'terdaftar',
                        'alamat' => $data['alamat'] ?? null,
                        'no_telp_ortu' => $data['no_hp'] ?? null,
                    ]);
                }

                $this->sessionService->updateStep($session, self::STEP_PAYMENT);
                return "**Biodata tersimpan.** Data tampil di menu **Dashboard > Biodata**.\n\n**Langkah 2: Pembayaran**\n\n" . $this->buildPaymentMessage($user);
            }
        }

        if ($currentStep === self::STEP_PAYMENT) {
            if (str_contains($normalizedLower, 'sudah bayar') || $normalizedLower === 'sudah bayar' || $normalizedLower === 'sudah') {
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
                $this->sessionService->updateStep($session, self::STEP_KAMAR);
                return "**Pembayaran tercatat dan status Terverifikasi.** Cek di **Clearance Slip > Pembayaran Pendaftaran**.\n\n**Langkah 3: Pilih Kamar**\n\n" . $this->buildKamarListMessage();
            }

            if (str_contains($normalizedLower, 'upload') || str_contains($normalizedLower, 'bukti') || str_contains($normalizedLower, 'sudah upload')) {
                $payment = PendaftaranPayment::where('user_id', $user->id)->first();
                if ($payment && $payment->bukti_path) {
                    $this->sessionService->updateStep($session, self::STEP_KAMAR);
                    return "**Bukti pembayaran Anda sudah diterima.** Status: menunggu verifikasi admin.\n\n**Langkah 3: Pilih Kamar**\n\n" . $this->buildKamarListMessage();
                }
                return "Gunakan tombol **Upload bukti** di bawah chat untuk mengirim foto bukti transfer. Setelah upload, ketik **sudah upload**.";
            }

            return $this->buildPaymentMessage($user) . "\n\n**Pilihan:**\n• Ketik **sudah bayar** jika sudah transfer.\n• Atau **upload bukti** pembayaran dengan tombol di bawah.";
        }

        if ($currentStep === self::STEP_KAMAR) {
            $nomorKamar = strtoupper(trim(preg_replace('/\s+/', '', $cleanMessage)));
            $kamar = PendaftaranKamar::firstOrCreate(
                ['nomor_kamar' => $nomorKamar],
                ['kapasitas' => 4, 'current_occupancy' => 0]
            );
            if ($kamar->current_occupancy >= $kamar->kapasitas) {
                return "Kamar **{$kamar->nomor_kamar}** sudah penuh. Pilih nomor kamar lain.\n\n" . $this->buildKamarListMessage();
            }
            
            $existing = PendaftaranRoomSelection::where('user_id', $user->id)->first();
            if ($existing && $existing->pendaftaran_kamar_id !== $kamar->id) {
                PendaftaranKamar::where('id', $existing->pendaftaran_kamar_id)->decrement('current_occupancy');
            }
            
            PendaftaranRoomSelection::updateOrCreate(
                ['user_id' => $user->id],
                ['pendaftaran_kamar_id' => $kamar->id]
            );
            $kamar->increment('current_occupancy');
            $this->sessionService->updateStep($session, self::STEP_KURIKULUM);
            
            $profile = PendaftaranProfile::where('user_id', $user->id)->first();
            return "**Kamar {$kamar->nomor_kamar}** terpilih.\n\n**Langkah 4: Kurikulum**\n\n" . $this->buildKurikulumMessage($user, $profile?->kelas_yang_didaftar);
        }

        if ($currentStep === self::STEP_KURIKULUM) {
            $this->sessionService->updateStep($session, self::STEP_DOKUMEN);
            return $this->buildDokumenMessage($user);
        }

        if ($currentStep === self::STEP_DOKUMEN) {
            $jenisList = [StudentDocument::JENIS_AKTE, StudentDocument::JENIS_KK, StudentDocument::JENIS_PAS_FOTO, StudentDocument::JENIS_RAPORT];
            foreach ($jenisList as $jenis) {
                StudentDocument::firstOrCreate(
                    ['user_id' => $user->id, 'jenis' => $jenis],
                    ['status' => 'verified']
                );
            }
            
            // Mark student docs as uploaded
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $student->update(['has_uploaded_docs' => true]);
                $nomorMakan = $student->getDiningNumberFormatted();
            } else {
                $nomorMakan = '001';
            }

            $this->sessionService->updateStep($session, null);

            return "**Dokumen Pendaftaran Berhasil Diunggah & Diverifikasi!** 🎉\n\n" .
                   "• **Jadwal Pelajaran**: Jadwal pelajaran Anda kini telah ditentukan dan siap dilihat di menu **Jadwal Pelajaran**.\n" .
                   "• **Nomor Makan**: Urutan nomor makan Anda di kantin adalah **{$nomorMakan}**. Cek menu **Dining** di navbar.\n\n" .
                   "Proses pendaftaran dengan Virtual Assistant telah selesai!";
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
        $student = Student::where('user_id', $user->id)->first();
        if (!$student || !$student->has_uploaded_docs) {
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
            $status = $payment->status === 'terverifikasi' ? 'Terverifikasi' : ($payment->bukti_path ? 'Menunggu verifikasi' : 'Menunggu');
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
        return "**Langkah 5: Upload Dokumen**\n\nSilakan konfirmasi upload dokumen pendaftaran Anda (Akte Kelahiran, KK, Pas Foto, Raport).\n\nKetik **upload** atau **selesai** untuk mengunggah dan menyelesaikan pendaftaran.";
    }
}
