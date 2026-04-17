<?php

namespace Niduranga\DevGuard\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService
{
    protected string $apiKey;
    // 2026 දී වඩාත් සුදුසු stable endpoint එක
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('dev-guard.api_key') ?? '';
    }

    public function generateTest(string $code, string $framework): string
    {
        $model = config('dev-guard.model', 'gemini-1.5-flash');

        $formattedModel = str_starts_with($model, 'models/') ? $model : "models/{$model}";

        $url = "{$this->baseUrl}/{$formattedModel}:generateContent?key={$this->apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                ['parts' => [['text' => $this->buildPrompt($code, $framework)]]]
            ]
        ]);

        if ($response->failed()) {
            $error = $response->json();

            if ($response->status() === 404) {
                throw new Exception("Model Not Found. Try using 'gemini-1.5-flash-latest' or 'gemini-pro' in your config. API Response: " . ($error['error']['message'] ?? 'Unknown Error'));
            }

            throw new Exception("Gemini AI Error: " . ($error['error']['message'] ?? $response->body()));
        }

        $responseText = $response->json('candidates.0.content.parts.0.text');

        return $this->extractCode($responseText);
    }

    protected function buildPrompt(string $code, string $framework): string
    {
        return "Write a professional {$framework} test for this Laravel Action: \n\n {$code} \n\n Respond ONLY with PHP code.";
    }

    protected function extractCode(string $response): string
    {
        $code = str_replace(['```php', '```', '<?php'], '', $response);
        return "<?php\n\n" . trim($code);
    }
}