<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Handoff;
use App\Services\Chatbot\FormAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class ChatSessionController extends Controller
{
    protected $chatbot;

    public function __construct(FormAssistant $chatbot)
    {
        $this->chatbot = $chatbot;
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
        if (RateLimiter::tooManyAttempts('send-message:'.$sessionId, 40)) {
            return response()->json(['message' => 'Terlalu banyak permintaan. Tunggu sebentar.'], 429);
        }
        RateLimiter::hit('send-message:'.$sessionId, 60); // 40 request per menit

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

        // Custom chatbot (rule-based NLU + slot filling, tanpa OpenAI).
        // Konfirmasi & koreksi ditangani langsung oleh FormAssistant.
        $aiResponse = $this->chatbot->getResponse($history->toArray(), $request->message, $currentSlots);

        // Pasca-pemrosesan: FormAssistant mengembalikan state slot lengkap
        $intent = $aiResponse['intent'] ?? 'faq';
        $replyText = $aiResponse['reply'] ?? 'Terjadi kesalahan.';
        $currentSlots = is_array($aiResponse['slots'] ?? null) ? $aiResponse['slots'] : $currentSlots;

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
            'reply' => $botMessage,
            'slots' => $currentSlots
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
