<?php

namespace App\Services\Chatbot\Response;

use Illuminate\Support\Facades\Lang;

class ResponseBuilder
{
    /**
     * Build standard response format for Vue frontend.
     */
    public function build(string $messageKey, array $data = [], array $quickReplies = [], ?string $action = null): array
    {
        // Get localized string, with fallbacks
        $template = __("chatbot.$messageKey", $data);

        // If the translation key doesn't exist, it returns the key itself.
        // We use this fallback if we just passed raw text instead of a key for simplicity in some flows.
        if ($template === "chatbot.$messageKey") {
            $template = $messageKey; 
        }

        // Variable replacement if not handled by Lang::get
        foreach ($data as $key => $val) {
            if (is_scalar($val)) {
                $template = str_replace('{' . $key . '}', $val, $template);
            }
        }

        // Format Rupiah (Post-processing)
        $template = preg_replace_callback('/Rp\s*(\d+)/', function ($matches) {
            return 'Rp ' . number_format((float)$matches[1], 0, ',', '.');
        }, $template);

        return [
            'text' => $template,
            'quick_replies' => $quickReplies,
            'action' => $action
        ];
    }
}
