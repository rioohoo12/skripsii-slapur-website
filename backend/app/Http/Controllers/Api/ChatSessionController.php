<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Handoff;
use App\Services\ChatbotService;
use App\Services\ApplicantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class ChatSessionController extends Controller
{
    protected $chatbot;
    protected $applicantService;

    public function __construct(ChatbotService $chatbot, ApplicantService $applicantService)
    {
        $this->chatbot = $chatbot;
        $this->applicantService = $applicantService;
    }

    public function createSession(Request $request)
    {
        $session = ChatSession::create([
            'session_id' => Str::uuid()->toString(),
            'status' => 'active',
            'registration_data' => json_encode([]), // initial slots
            'last_activity_at' => now(),
        ]);

        return response()->json([
            'message' => 'Sesi chat berhasil dibuat',
            'data' => $session
        ], 201);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:chat_sessions,session_id',
            'message' => 'required|string|max:500' // proteksi laju & injeksi dasar
        ]);

        $sessionId = $request->session_id;

        // Rate limiting (Perlindungan laju)
        if (RateLimiter::tooManyAttempts('send-message:'.$sessionId, 10)) {
            return response()->json(['message' => 'Terlalu banyak permintaan. Tunggu sebentar.'], 429);
        }
        RateLimiter::hit('send-message:'.$sessionId, 60); // 10 request per menit

        $session = ChatSession::where('session_id', $sessionId)->first();
        
        if ($session->status === 'handed_off') {
            // Jika sudah handoff, biarkan staf yang membalas. (Pesan user tetap disimpan)
            ChatMessage::create([
                'session_id' => $sessionId,
                'role' => 'user',
                'sender_type' => 'user',
                'message' => $request->message,
            ]);
            return response()->json(['message' => 'Pesan terkirim ke staf']);
        }

        // Simpan pesan user
        ChatMessage::create([
            'session_id' => $sessionId,
            'role' => 'user',
            'sender_type' => 'user',
            'message' => $request->message,
        ]);

        // Ambil riwayat chat (10 terakhir)
        $history = ChatMessage::where('session_id', $sessionId)
            ->where('role', '!=', 'system')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        $currentSlots = json_decode($session->registration_data, true) ?: [];

        // Deteksi konfirmasi (Hardcoded logic untuk bypass GPT jika menunggu konfirmasi)
        if (isset($currentSlots['awaiting_confirmation']) && $currentSlots['awaiting_confirmation'] === true) {
            $userMsg = strtolower($request->message);
            if (in_array($userMsg, ['ya', 'benar', 'betul', 'yes', 'setuju', 'oke'])) {
                // Buat pendaftar
                $applicant = $this->applicantService->createApplicant($currentSlots);
                
                $replyMsg = "Terima kasih! Pendaftaran Anda berhasil dicatat dengan Nomor Pendaftaran: **{$applicant->nomor_pendaftaran}**. Silakan simpan nomor ini. Ketik 'bayar' untuk melihat informasi tagihan pendaftaran.";
                
                // Clear slots & update session
                $session->update([
                    'registration_data' => json_encode([]),
                    'applicant_id' => $applicant->id
                ]);

                $botMessage = ChatMessage::create([
                    'session_id' => $sessionId,
                    'role' => 'assistant',
                    'sender_type' => 'bot',
                    'intent' => 'registration_complete',
                    'message' => $replyMsg,
                ]);

                return response()->json(['reply' => $botMessage]);
            } elseif (in_array($userMsg, ['tidak', 'salah', 'koreksi', 'ubah'])) {
                $currentSlots['awaiting_confirmation'] = false;
                $session->update(['registration_data' => json_encode($currentSlots)]);
                
                $botMessage = ChatMessage::create([
                    'session_id' => $sessionId,
                    'role' => 'assistant',
                    'sender_type' => 'bot',
                    'intent' => 'correction',
                    'message' => 'Baik, bagian mana yang ingin Anda koreksi? (nama/usia/email/no_hp)',
                ]);
                return response()->json(['reply' => $botMessage]);
            }
        }

        // Panggil GPT
        $aiResponse = $this->chatbot->getResponse($history->toArray(), $request->message, $currentSlots);

        // Pasca-pemrosesan
        $newSlots = $aiResponse['slots'] ?? [];
        $intent = $aiResponse['intent'] ?? 'faq';
        $replyText = $aiResponse['reply'] ?? 'Terjadi kesalahan.';

        // Gabungkan slot
        if (is_array($newSlots)) {
            foreach ($newSlots as $key => $val) {
                if ($val !== 'belum diisi' && !empty($val)) {
                    $currentSlots[$key] = $val;
                }
            }
        }

        if ($intent === 'summary_confirmation') {
            $currentSlots['awaiting_confirmation'] = true;
        }

        $session->update([
            'registration_data' => json_encode($currentSlots),
            'last_activity_at' => now(),
        ]);

        if ($intent === 'handoff') {
            $session->update(['status' => 'handed_off']);
            Handoff::create([
                'chat_session_id' => $session->id,
                'status' => 'pending',
                'reason' => 'User requested human assistance'
            ]);
            $replyText = "Menghubungkan Anda dengan staf kami. Mohon tunggu sebentar...";
        }

        // Simpan balasan bot
        $botMessage = ChatMessage::create([
            'session_id' => $sessionId,
            'role' => 'assistant',
            'sender_type' => 'bot',
            'intent' => $intent,
            'message' => $replyText,
        ]);

        return response()->json([
            'message' => 'Pesan terkirim',
            'reply' => $botMessage
        ]);
    }

    public function getHistory($sessionId)
    {
        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
        
        $messages = ChatMessage::where('session_id', $session->session_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'session_id' => $session->session_id,
            'status' => $session->status,
            'messages' => $messages
        ]);
    }
}
