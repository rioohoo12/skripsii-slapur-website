<?php

namespace App\Services\Chatbot;

class PostprocessingService
{
    /**
     * Postprocess & sanitize chatbot response text.
     */
    public function process(string $reply): string
    {
        $text = trim($reply);

        // 1. Masking Sensitive Patterns (Passwords, Hashes, Secrets)
        $text = preg_replace('/(password|token|secret|hash)\s*[:=]\s*[^\s]+/i', '$1: [DISEMBUNYIKAN]', $text);

        // 2. Remove markdown code fence wrapper if model returns raw ```
        if (str_starts_with($text, '```json') || str_starts_with($text, '```html')) {
            $text = preg_replace('/^```[a-z]*\s*/', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);
        }

        return $text;
    }
}
