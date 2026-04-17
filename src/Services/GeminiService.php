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
        $model = config('dev-guard.model', 'gemini-1.5-flash');

        try {
            return $this->makeRequest($model, $code, $framework);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), '404')) {
                return $this->makeRequest('gemini-1.5-flash-latest', $code, $framework);
            }
            throw $e;
        }
    }

    protected function makeRequest(string $model, string $code, string $framework): string
    {
        $modelName = str_starts_with($model, 'models/') ? $model : "models/{$model}";

        $url = "{$this->baseUrl}/{$modelName}:generateContent?key={$this->apiKey}";

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $this->buildPrompt($code, $framework)]]]
                ]
            ]);

        if ($response->failed()) {
            throw new Exception("Status: " . $response->status() . " | " . $response->body());
        }

        $responseText = $response->json('candidates.0.content.parts.0.text');

        if (!$responseText) {
            throw new Exception("Gemini returned an empty response. Response Data: " . json_encode($response->json()));
        }

        return $this->extractCode($responseText);
    }

    protected function buildPrompt(string $code, string $framework): string
    {
        return "You are a Senior Full-stack Developer. Write a professional {$framework} test for the following Laravel Action class. 
                - Use proper Mocking.
                - Follow SOLID and TDD principles.
                - Provide ONLY the executable PHP code without any markdown formatting or comments.
                
                Action Class Code:
                {$code}";
    }

    protected function extractCode(string $response): string
    {
        $code = str_replace(['```php', '```', '<?php'], '', $response);
        return "<?php\n\n" . trim($code);
    }
}