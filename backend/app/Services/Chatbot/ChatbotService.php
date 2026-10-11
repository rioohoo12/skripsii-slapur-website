<?php

namespace App\Services\Chatbot;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use App\Services\Chatbot\Session\SessionManager;
use Illuminate\Support\Str;

class ChatbotService
{
    public function __construct(
        protected GuardrailService $guardrail,
        protected PreprocessingService $preprocessor,
        protected IntentRouter $router,
        protected LocalDataResolver $localDataResolver,
        protected FormAssistant $formAssistant,
        protected PromptBuilder $promptBuilder,
        protected OpenAIService $openAI,
        protected PostprocessingService $postprocessor,
        protected SessionManager $sessionManager
    ) {}

    /**
     * Primary orchestrator for handling user messages.
     */
    public function handleMessage(string $messageText, ?string $sessionId = null, ?User $user = null, ?string $mode = null): array
    {
        $startTime = microtime(true);
        $role = strtolower($user?->role ?? 'umum');

        // 1. Guardrail Validation (Keamanan & Batasan Input)
        $guardCheck = $this->guardrail->validate($messageText);
        if (!$guardCheck['is_valid']) {
            return [
                'session_id' => $sessionId ?: Str::uuid()->toString(),
                'reply' => $guardCheck['reason'],
                'intent' => 'blocked',
                'source' => 'guardrail',
                'slots' => [],
            ];
        }

        // 2. Session Management
        $session = $this->sessionManager->getOrCreateSession($sessionId, $user?->id);
        $session->update(['role' => $role]);

        $currentSlots = json_decode($session->slots, true) ?: [];
        if ($mode) {
            $currentSlots['_mode'] = $mode;
        }
        $history = $this->sessionManager->getRecentContext($session, 5);

        // 3. Preprocessing (Cleaning & Intent Detection)
        $prep = $this->preprocessor->preprocess($messageText);
        $intent = $prep['intent'];

        // Save User Message Log
        ChatMessage::create([
            'session_id' => $session->session_id,
            'role' => 'user',
            'sender_type' => 'user',
            'message' => $messageText,
            'pesan' => $messageText,
            'intent' => $intent,
            'source' => 'user',
            'sumber' => 'user',
        ]);

        // 4. Intent Routing
        $route = $this->router->route($intent, $messageText, $user, $currentSlots);
        $replyText = '';
        $source = 'local';
        $tokens = 0;

        switch ($route['type']) {
            case 'action':
                // Form Assistant Slot Filling (Pendaftaran)
                $formResult = $this->formAssistant->getResponse($history, $messageText, $currentSlots);
                $replyText = $formResult['reply'];
                $intent = $formResult['intent'];
                $currentSlots = $formResult['slots'];
                $source = 'action';
                break;

            case 'local_data':
                $replyText = $route['resolved_data']['summary'];
                $source = 'local_db';
                break;

            case 'faq':
                $replyText = $route['answer'];
                $source = 'knowledge_base';
                break;

            case 'llm':
            default:
                // Resolve local data context if available
                $localContext = $this->localDataResolver->resolve($intent, $user, $currentSlots, $messageText);
                $systemPrompt = $this->promptBuilder->build($user, $localContext);

                // Call OpenAI with 10s timeout & 2 retries
                $llmResult = $this->openAI->generateResponse($systemPrompt, $messageText, $history);

                if ($llmResult['success'] && !empty($llmResult['reply'])) {
                    $replyText = $llmResult['reply'];
                    $source = 'llm';
                    $tokens = $llmResult['tokens'];
                } else {
                    // Fallback message when OpenAI API is unconfigured/down/timeout
                    $replyText = $this->getFallbackMessage($messageText, $intent, $user);
                    $source = 'fallback';
                }
                break;
        }

        // 5. Postprocessing (Sanitization & Filtering)
        $cleanReply = $this->postprocessor->process($replyText);

        // 6. Update Session State
        $this->sessionManager->updateState(
            $session,
            $session->current_flow ?: 'default',
            $session->current_step ?: 'init',
            $currentSlots
        );

        $latencyMs = round((microtime(true) - $startTime) * 1000);

        // 7. Save Assistant Message Log
        ChatMessage::create([
            'session_id' => $session->session_id,
            'role' => 'assistant',
            'sender_type' => 'bot',
            'message' => $cleanReply,
            'pesan' => $messageText,
            'respons' => $cleanReply,
            'intent' => $intent,
            'source' => $source,
            'sumber' => $source,
            'tokens' => $tokens,
            'latency_ms' => $latencyMs,
        ]);

        return [
            'session_id' => $session->session_id,
            'reply' => $cleanReply,
            'intent' => $intent,
            'source' => $source,
            'slots' => $currentSlots,
        ];
    }

    /**
     * Fallback message when LLM/API is unavailable or times out.
     */
    protected function getFallbackMessage(string $message, string $intent, ?User $user = null): string
    {
        $role = strtolower($user?->role ?? 'umum');
        return match ($role) {
            'staff', 'administrasi' => "Halo Staff Administrasi! Anda dapat menanyakan panduan menu, ketik \"info siswa [nama]\" untuk mencari data murid lengkap, \"berkas pending\", atau \"antrean pembayaran\".",
            'staff_asrama' => "Halo Staff Asrama! Anda dapat menanyakan panduan menu asrama, \"total murid asrama putra\", \"total murid asrama putri\", \"kamar kosong\", atau \"persetujuan kamar\".",
            'staff_kafetaria' => "Halo Staff Kafetaria! Anda dapat menanyakan panduan menu kafetaria, \"menu hari ini\", \"rekap siswa makan\", atau \"panduan scan\".",
            'siswa', 'murid' => "Halo! Kamu dapat menanyakan panduan menu siswa, \"tagihan saya\", \"jadwal pelajaran\", \"kamar asrama\", atau ketik \"staf\" untuk bantuan.",
            'guru' => "Halo Bapak/Ibu Guru! Anda dapat menanyakan \"jadwal mengajar\", \"panduan absensi\", atau \"panduan nilai\".",
            default => "Maaf, sistem AI sedang sibuk. Anda dapat menanyakan seputar Pendaftaran, Biaya & SPP, Asrama, atau ketik \"staf\" untuk bantuan operator.",
        };
    }
}
