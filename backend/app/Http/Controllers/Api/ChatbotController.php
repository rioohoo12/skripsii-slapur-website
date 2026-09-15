<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Chatbot\ChatMessageService;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbotService,
        private ChatMessageService $messageService
    ) {}

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
     */
    public function message(Request $request): JsonResponse
    {
        $this->ensureJsonInput($request);

        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $message = trim($request->input('message'));
        $sessionId = $request->input('session_id');

        // Resolve user dari Bearer token
        $user = $request->user();
        if (!$user && $request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            $user = $token?->tokenable;
        }

        $result = $this->chatbotService->handleMessage($message, $sessionId, $user);

        return response()->json($result);
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

        $messages = $this->messageService->getHistory($sessionId);

        return response()->json(['messages' => $messages]);
    }

    /**
     * POST /api/chatbot/clear
     * Body: { "session_id": "..." }
     */
    public function clear(Request $request): JsonResponse
    {
        $this->ensureJsonInput($request);

        $sessionId = $request->input('session_id');
        if (!$sessionId) {
            return response()->json(['message' => 'session_id diperlukan'], 422);
        }

        $deletedCount = $this->messageService->clearHistory($sessionId);

        return response()->json([
            'message' => 'Chat berhasil dihapus. Anda bisa memulai percakapan baru.',
            'deleted_count' => $deletedCount,
        ]);
    }
}
