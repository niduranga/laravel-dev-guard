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
        $model = config('dev-guard.model', 'gemini-2.5-flash');

        if (str_contains($model, '1.5-flash')) {
            $model = 'gemini-2.5-flash';
        }

        return $this->makeRequest($model, $code, $framework);
    }

    protected function makeRequest(string $model, string $code, string $framework): string
    {
        $url = "{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}";

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(120)
            ->connectTimeout(20)
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $this->buildPrompt($code, $framework)]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                ]
            ]);

        if ($response->failed()) {
            throw new Exception("Gemini API Error: Status " . $response->status() . " | " . $response->body());
        }

        $responseText = $response->json('candidates.0.content.parts.0.text');

        if (!$responseText) {
            throw new Exception("Empty response from Gemini. Check logic complexity or API limits.");
        }

        return $this->extractCode($responseText);
    }

    protected function buildPrompt(string $code, string $framework): string
    {
        return "You are a Senior Full-stack Developer. Write a high-performance, clean {$framework} test for this Laravel Action class.
                - Use Mockery for dependencies.
                - Follow SOLID principles and Clean Architecture.
                - Use professional-grade assertions.
                - RETURN ONLY THE RAW PHP CODE. NO EXPLANATIONS.

                Code to test:
                {$code}";
    }

    protected function extractCode(string $response): string
    {
        $code = preg_replace('/^```php\s*|```$/m', '', $response);
        $code = str_replace('<?php', '', $code);

        return "<?php\n\n" . trim($code);
    }
}