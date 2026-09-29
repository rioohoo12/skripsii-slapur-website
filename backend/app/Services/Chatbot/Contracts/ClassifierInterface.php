<?php

namespace App\Services\Chatbot\Contracts;

interface ClassifierInterface
{
    /**
     * Classify the intent of the preprocessed text.
     *
     * @param string $text
     * @return array Returns an array with 'intent' and 'confidence'
     */
    public function classify(string $text): array;
}
