<?php

namespace App\Services\Chatbot\Contracts;

interface ExtractorInterface
{
    /**
     * Extract entities from the text based on current session context.
     *
     * @param string $text
     * @param array $context
     * @return array
     */
    public function extract(string $text, array $context = []): array;
}
