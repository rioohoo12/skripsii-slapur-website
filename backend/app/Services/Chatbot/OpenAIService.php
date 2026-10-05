<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected string $model;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    /**
     * Send messages array to OpenAI Chat Completion API.
     */
    public function generateResponse(string $systemPrompt, string $userMessage, array $history = []): array
    {
        if (empty($this->apiKey) || $this->apiKey === 'your_openai_api_key_here') {
            Log::info('OpenAI API Key not configured. Using rule-based fallback.');
            return [
                'success' => false,
                'source' => 'local_fallback',
                'reply' => null,
                'tokens' => 0,
            ];
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($history as $h) {
            $role = ($h['role'] ?? 'user') === 'user' ? 'user' : 'assistant';
            $content = $h['pesan'] ?? $h['message'] ?? $h['text'] ?? '';
            if ($content) {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(10)      // Timeout 10 detik
            ->retry(2, 1000)   // Retry 2 kali dengan jeda 1 detik
            ->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $reply = $response->json('choices.0.message.content');
                $tokens = $response->json('usage.total_tokens', 0);

                return [
                    'success' => true,
                    'source' => 'llm',
                    'reply' => trim($reply),
                    'tokens' => $tokens,
                ];
            }

            Log::error('OpenAI API Call Failed: ' . $response->body());
            return [
                'success' => false,
                'source' => 'fallback',
                'reply' => null,
                'tokens' => 0,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'source' => 'fallback',
                'reply' => null,
                'tokens' => 0,
            ];
        }
    }
}
