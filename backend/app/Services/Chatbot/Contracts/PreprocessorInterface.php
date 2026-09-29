<?php

namespace App\Services\Chatbot\Contracts;

interface PreprocessorInterface
{
    /**
     * Preprocess the input text before passing to NLP modules.
     *
     * @param string $text
     * @return array Returns an array with 'clean' (for classification) and 'normalized' (for extraction)
     */
    public function process(string $text): array;
}
