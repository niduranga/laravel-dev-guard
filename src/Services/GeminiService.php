<?php

namespace Niduranga\DevGuard\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('dev-guard.api_key') ?? '';
    }

    public function generateTest(string $code, string $framework): string
    {
        $model = config('dev-guard.model', 'gemini-1.5-flash-latest');

        $modelName = str_starts_with($model, 'models/') ? $model : "models/{$model}";

        $prompt = $this->buildPrompt($code, $framework);
        $url = "{$this->baseUrl}/{$modelName}:generateContent?key={$this->apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
            ]
        ]);

        if ($response->failed()) {
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? "Unknown API Error";
            throw new Exception("Gemini AI Error: " . $errorMessage);
        }

        $responseText = $response->json('candidates.0.content.parts.0.text');

        if (!$responseText) {
            throw new Exception("Invalid response structure from Gemini API.");
        }

        return $this->extractCode($responseText);
    }

    protected function buildPrompt(string $code, string $framework): string
    {
        return "As a Senior Full-stack Developer, generate a professional {$framework} test for the following Laravel Action.
                - Mock all models and external services.
                - Ensure strict SOLID compliance.
                - Only output raw PHP code. Do not include markdown formatting or explanations.
                
                Code:
                {$code}";
    }

    protected function extractCode(string $response): string
    {
        $code = str_replace(['```php', '```', '<?php'], '', $response);
        return "<?php\n\n" . trim($code);
    }
}