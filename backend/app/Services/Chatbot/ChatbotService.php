<?php

namespace App\Services\Chatbot;

use App\Models\ChatMessage;
use App\Services\Chatbot\Session\SessionManager;
use App\Services\ChatbotCoreService; // Existing internal NLP service from Sprint 2
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected $sessionManager;
    protected $nlpEngine;
    protected $llmFallback;

    public function __construct(SessionManager $sessionManager, ChatbotCoreService $nlpEngine, \App\Services\Chatbot\Fallback\LLMFallback $llmFallback)
    {
        $this->sessionManager = $sessionManager;
        $this->nlpEngine = $nlpEngine;
        $this->llmFallback = $llmFallback;
    }

    /**
     * Orchestrator to handle incoming chat messages.
     */
    public function handle(?string $sessionId, string $text, ?int $userId = null)
    {
        $startTime = microtime(true);

        // 1. Session Management
        $session = $this->sessionManager->getOrCreateSession($sessionId, $userId);
        $context = $this->sessionManager->getRecentContext($session);
        
        $currentSlots = json_decode($session->slots, true) ?: [];

        // Save User Message
        $userMsg = ChatMessage::create([
            'session_id' => $session->session_id,
            'role' => 'user',
            'text' => $text,
            'source' => 'user'
        ]);

        // 2. Preprocessing & NLU (Delegating to NLP Engine)
        $nlpResult = $this->nlpEngine->processMessage($text, ['awaiting_slot' => $session->current_step]);
        $intent = $nlpResult['intent'] ?? 'tidak_dikenali';
        $entities = $nlpResult['entities'] ?? [];
        $confidence = $nlpResult['confidence'] ?? 0.85; // Default simulasi
        
        // Cek Confidence Threshold
        if ($confidence < config('chatbot.confidence_threshold', 0.6)) {
            $intent = 'tidak_dikenali';
        }

        // Merge Entities to Slots
        foreach ($entities as $k => $v) {
            $currentSlots[$k] = $v;
        }

        $source = 'nlp';
        
        // 3. Dialog Management
        if ($intent === 'tidak_dikenali') {
            $replyText = $this->llmFallback->generateResponse($text, $context);
            $action = null;
            $source = 'gpt';
        } else {
            $flowResult = $this->routeToFlow($session, $intent, $currentSlots, $text);
            $replyText = $flowResult['reply'];
            $action = $flowResult['action'] ?? null;
        }

        // 4. Update Session State
        $this->sessionManager->updateState($session, $session->current_flow, $session->current_step, $currentSlots);

        // 5. Response & Logging
        $latencyMs = round((microtime(true) - $startTime) * 1000);

        $botMsg = ChatMessage::create([
            'session_id' => $session->session_id,
            'role' => 'bot',
            'text' => $replyText,
            'intent' => $intent,
            'confidence' => $confidence,
            'entities' => json_encode($entities),
            'source' => $source,
            'latency_ms' => $latencyMs
        ]);

        return [
            'session_id' => $session->session_id,
            'reply' => $replyText,
            'intent' => $intent,
            'action' => $action
        ];
    }

    protected function routeToFlow($session, $intent, &$slots, $text)
    {
        // Simple router logic that would normally map to Dialog\Flows\*
        if ($intent === 'mulai_daftar' || $session->current_flow === 'registration') {
            $session->current_flow = 'registration';
            $flow = app()->make(\App\Services\Chatbot\Dialog\Flows\RegistrationFlow::class);
            $result = $flow->handle($session, $intent, $slots, $text);
            return $result;
        }
        
        if ($intent === 'bayar' || $session->current_flow === 'payment') {
            $session->current_flow = 'payment';
            $flow = app()->make(\App\Services\Chatbot\Dialog\Flows\PaymentFlow::class);
            $result = $flow->handle($session, $intent, $slots, $text);
            return $result;
        }
        
        if ($intent === 'minta_staf') {
            $session->status = 'handed_off';
            return ['reply' => "Menghubungkan Anda ke staf admin..."];
        }

        if ($intent === 'tidak_dikenali' && config('chatbot.enable_llm_fallback')) {
            // Fallback Logic
            return ['reply' => "Maaf saya kurang paham. Anda bisa tanya seputar info pendaftaran."];
        }

        // Default FAQ
        return ['reply' => "Info pendaftaran: Biaya 250rb, asrama tersedia. Ketik 'mulai daftar' untuk registrasi."];
    }
}
