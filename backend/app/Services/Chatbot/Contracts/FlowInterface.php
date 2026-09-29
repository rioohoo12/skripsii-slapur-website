<?php

namespace App\Services\Chatbot\Contracts;

use App\Models\ChatSession;

interface FlowInterface
{
    /**
     * Handle the conversational flow step.
     *
     * @param ChatSession $session
     * @param string $intent
     * @param array $entities
     * @param string $text
     * @return array Returns an array with 'reply' and optionally 'action'
     */
    public function handle(ChatSession $session, string $intent, array $entities, string $text): array;
}
