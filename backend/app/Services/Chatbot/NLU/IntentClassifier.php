<?php

namespace App\Services\Chatbot\NLU;

use App\Services\Chatbot\Contracts\ClassifierInterface;
use Phpml\ModelManager;
use Illuminate\Support\Facades\Log;

class IntentClassifier implements ClassifierInterface
{
    protected $modelPath;
    protected $pipeline;

    public function __construct()
    {
        $this->modelPath = storage_path('app/chatbot/intent_model.phpml');
        $this->loadModel();
    }

    protected function loadModel()
    {
        if (file_exists($this->modelPath)) {
            try {
                $modelManager = new ModelManager();
                $this->pipeline = $modelManager->restoreFromFile($this->modelPath);
            } catch (\Exception $e) {
                Log::error('Failed to load Chatbot Intent Model: ' . $e->getMessage());
            }
        }
    }

    public function classify(string $text): array
    {
        if (!$this->pipeline) {
            return [
                'intent' => 'tidak_dikenali',
                'confidence' => 0.0,
                'alternatives' => []
            ];
        }

        try {
            // Predict returns the highest probability class
            $predictedIntent = $this->pipeline->predict([$text])[0];
            
            // PHP-ML NaiveBayes predict() doesn't expose confidence probabilities out of the box in Pipeline.
            // For Skripsi, we simulate a confidence score. If it matches strongly, it's 0.9.
            // If the model was customized or we use an advanced predictor, we would get real probabilities.
            $confidence = 0.85; // Simulated confidence since naive bayes pipeline only returns label
            
            if ($predictedIntent === 'tidak_dikenali') {
                $confidence = 0.4;
            }

            return [
                'intent' => $predictedIntent,
                'confidence' => $confidence,
                'alternatives' => []
            ];
        } catch (\Exception $e) {
            Log::error('Classification error: ' . $e->getMessage());
            return [
                'intent' => 'tidak_dikenali',
                'confidence' => 0.0,
                'alternatives' => []
            ];
        }
    }
}
