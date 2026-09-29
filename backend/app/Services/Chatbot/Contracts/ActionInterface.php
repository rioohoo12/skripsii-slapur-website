<?php

namespace App\Services\Chatbot\Contracts;

use App\Models\ChatSession;

interface ActionInterface
{
    /**
     * Execute a specific action (e.g. database insert, API call) based on filled slots.
     *
     * @param ChatSession $session
     * @param array $data
     * @return mixed
     */
    public function execute(ChatSession $session, array $data);
}
