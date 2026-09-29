<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chatbot Core Configuration
    |--------------------------------------------------------------------------
    */

    // Minimum confidence required to accept an intent (0.0 to 1.0)
    'confidence_threshold' => env('CHATBOT_CONFIDENCE_THRESHOLD', 0.6),

    // Maximum number of times the bot will try to ask for a specific slot before falling back or aborting
    'max_slot_retries' => env('CHATBOT_MAX_SLOT_RETRIES', 3),

    // Session timeout in minutes. After this time, a new session is created or state is reset.
    'session_timeout_minutes' => env('CHATBOT_SESSION_TIMEOUT', 30),

    // Whether to fallback to LLM (e.g. OpenAI GPT) if intent is not recognized or confidence is too low
    'enable_llm_fallback' => env('CHATBOT_ENABLE_LLM_FALLBACK', true),

    // Number of recent messages to keep in short-term context
    'context_history_limit' => env('CHATBOT_CONTEXT_HISTORY_LIMIT', 5),
];
