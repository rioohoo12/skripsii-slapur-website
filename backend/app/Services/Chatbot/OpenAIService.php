<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private ?string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->model = config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-mini'));
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
    }

    /**
     * Memeriksa apakah OpenAI API Key dikonfigurasi.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && strlen(trim($this->apiKey)) > 10;
    }

    /**
     * Kirim permintaan penyelesaian chat ke OpenAI API.
     */
    public function generateCompletion(string $systemPrompt, string $userPrompt): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            Log::warning('OpenAI API Request Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('OpenAI Exception', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
