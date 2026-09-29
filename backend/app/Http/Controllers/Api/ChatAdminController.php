<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Handoff;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\KnowledgeItem;

class ChatAdminController extends Controller
{
    // Handoff management
    public function getHandoffs()
    {
        $handoffs = Handoff::with('session')->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $handoffs]);
    }

    public function getChatLog($sessionId)
    {
        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
        $messages = ChatMessage::where('session_id', $session->session_id)->orderBy('created_at', 'asc')->get();
        
        // Data masking for sensitive data (e.g. phone numbers)
        $messages->transform(function ($msg) {
            if ($msg->sender_type === 'user') {
                // Mask phone numbers (simple regex for consecutive digits)
                $msg->message = preg_replace('/(\d{3})\d{4,}(\d{2})/', '$1*****$2', $msg->message);
            }
            return $msg;
        });

        return response()->json(['data' => $messages]);
    }

    public function replyHandoff(Request $request, $sessionId)
    {
        $request->validate(['message' => 'required|string']);

        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
        
        // Send message as staff
        $msg = ChatMessage::create([
            'session_id' => $session->session_id,
            'role' => 'assistant',
            'sender_type' => 'staff',
            'message' => $request->message
        ]);

        return response()->json(['message' => 'Pesan terkirim', 'data' => $msg]);
    }

    // FAQ / Knowledge Base management
    public function getFAQs()
    {
        return response()->json(['data' => KnowledgeItem::all()]);
    }

    public function storeFAQ(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'topic' => 'nullable|string'
        ]);

        $faq = KnowledgeItem::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'topic' => $request->topic ?? 'general',
            'trigger_keywords' => json_encode([]), // legacy field
            'response' => $request->answer // legacy field mapped
        ]);

        return response()->json(['message' => 'FAQ ditambahkan', 'data' => $faq]);
    }
}
