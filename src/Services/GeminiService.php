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

    /**
     * @throws Exception
     */
    public function generateTest(string $code, string $framework): string
    {
        $model = config('dev-guard.model', 'gemini-1.5-flash');

        $modelPath = str_contains($model, 'models/') ? $model : "models/{$model}";

        $prompt = $this->buildPrompt($code, $framework);

        $url = "{$this->baseUrl}/{$modelPath}:generateContent?key={$this->apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->failed()) {
            throw new Exception("AI Request Failed: " . $response->body());
        }

        $responseText = $response->json('candidates.0.content.parts.0.text');

        if (!$responseText) {
            throw new Exception("Empty response from Gemini. Check your API Key or Prompt.");
        }

        return $this->extractCode($responseText);
    }

    protected function buildPrompt(string $code, string $framework): string
    {
        return "As an expert Laravel Developer, generate a high-performance {$framework} test for the following Action class. 
                Requirements:
                - Use proper Mocking for external services and models.
                - Follow PSR-12 coding standards.
                - Ensure University-level logic rigor and SOLID principles.
                - Provide only the executable PHP code.
                - Do NOT include markdown blocks like ```php or ```.
                
                Action Class Code:
                {$code}";
    }

    protected function extractCode(string $response): string
    {
        $code = str_replace(['```php', '```javascript', '```'], '', $response);
        return trim($code);
    }
}