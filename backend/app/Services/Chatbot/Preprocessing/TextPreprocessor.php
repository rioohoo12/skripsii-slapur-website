<?php

namespace App\Services\Chatbot\Preprocessing;

use App\Services\Chatbot\Contracts\PreprocessorInterface;
use Sastrawi\Stemmer\StemmerFactory;
use Sastrawi\StopWordRemover\StopWordRemoverFactory;

class TextPreprocessor implements PreprocessorInterface
{
    protected $stemmer;
    protected $stopWordRemover;
    protected $slangDictionary;

    public function __construct()
    {
        $stemmerFactory = new StemmerFactory();
        $this->stemmer = $stemmerFactory->createStemmer();
        
        $stopWordFactory = new StopWordRemoverFactory();
        $this->stopWordRemover = $stopWordFactory->createStopWordRemover();
        
        $this->loadSlangDictionary();
    }

    protected function loadSlangDictionary()
    {
        $path = storage_path('app/chatbot/slang.json');
        if (file_exists($path)) {
            $this->slangDictionary = json_decode(file_get_contents($path), true) ?? [];
        } else {
            $this->slangDictionary = [];
        }
    }

    public function process(string $text): array
    {
        // 1. Cleaning & Case folding
        $text = strtolower(trim($text));
        
        // Save original raw text
        $raw = $text;

        // 2. Normalisasi slang (used for both classification and extraction)
        $words = explode(' ', $text);
        $normalizedWords = [];
        foreach ($words as $word) {
            // Remove punctuation for slang checking but keep it in the word
            $cleanWord = preg_replace('/[^\w]/', '', $word);
            if (isset($this->slangDictionary[$cleanWord])) {
                // Replace but try to keep original spacing/punctuation if possible (simplified here)
                $normalizedWords[] = str_replace($cleanWord, $this->slangDictionary[$cleanWord], $word);
            } else {
                $normalizedWords[] = $word;
            }
        }
        $normalizedText = implode(' ', $normalizedWords);

        // 3. Cleaning punctuation for classification
        $cleanText = preg_replace('/[^\w\s]/', ' ', $normalizedText);
        $cleanText = preg_replace('/\s+/', ' ', $cleanText);

        // 4. Stopword removal
        $cleanText = $this->stopWordRemover->remove($cleanText);

        // 5. Stemming (Sastrawi)
        $cleanText = $this->stemmer->stem($cleanText);

        return [
            'raw' => $raw,
            'normalized' => $normalizedText, // Used for Entity Extraction (keeps punctuation, numbers, names)
            'clean' => $cleanText            // Used for Intent Classification (stemmed, no stopwords)
        ];
    }
}
