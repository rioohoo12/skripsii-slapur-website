<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessageRequest;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;

class ChatbotCoreController extends Controller
{
    protected $chatbot;

    public function __construct(ChatbotService $chatbot)
    {
        $this->chatbot = $chatbot;
    }

    /**
     * Handle incoming chatbot message.
     */
    public function message(ChatMessageRequest $request): JsonResponse
    {
        $userId = auth('sanctum')->check() ? auth('sanctum')->id() : null;
        
        $response = $this->chatbot->handle(
            $request->validated('session_id'),
            $request->validated('message'),
            $userId
        );

        return response()->json($response);
    }
}
