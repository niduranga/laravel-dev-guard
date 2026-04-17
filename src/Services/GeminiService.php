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
        return $this->makeRequest($model, $code, $framework);
    }

    protected function makeRequest(string $model, string $code, string $framework): string
    {
        $modelName = "models/{$model}";
        $url = "{$this->baseUrl}/{$modelName}:generateContent?key={$this->apiKey}";

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
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
            throw new Exception("Status: " . $response->status() . " | " . $response->body());
        }

        $responseText = $response->json('candidates.0.content.parts.0.text');

        if (!$responseText) {
            throw new Exception("Gemini returned an empty response. Check if your API Key has safety filters enabled.");
        }

        return $this->extractCode($responseText);
    }

    protected function buildPrompt(string $code, string $framework): string
    {
        return "You are a Senior Full-stack Developer. Write a high-performance PHPUnit test for this Laravel Action. 
                Use Mockery, follow SOLID principles, and Clean Architecture. 
                Return ONLY the raw PHP code.
                
                Code:
                {$code}";
    }

    protected function extractCode(string $response): string
    {
        $code = str_replace(['```php', '```', '<?php'], '', $response);
        return "<?php\n\n" . trim($code);
    }
}