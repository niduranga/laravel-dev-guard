<?php

namespace Niduranga\DevGuard\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1';

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

        $modelName = str_starts_with($model, 'models/') ? $model : "models/{$model}";

        $prompt = $this->buildPrompt($code, $framework);

        $url = "{$this->baseUrl}/{$modelName}:generateContent?key={$this->apiKey}";

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
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? $response->body();
            throw new Exception("Gemini AI Error: " . $errorMessage);
        }

        $responseText = $response->json('candidates.0.content.parts.0.text');

        if (!$responseText) {
            throw new Exception("Failed to retrieve text from Gemini response.");
        }

        return $this->extractCode($responseText);
    }

    protected function buildPrompt(string $code, string $framework): string
    {
        return "You are a Senior Full-stack Developer. Generate a technical {$framework} test for this Laravel Action class.
                Requirements:
                - Mock all external dependencies and models.
                - Use PSR-12 coding standards and SOLID principles.
                - Ensure high performance and coverage.
                - RETURN ONLY THE PHP CODE. NO EXPLANATIONS. NO MARKDOWN BLOCKS.
                
                Action Class:
                {$code}";
    }

    protected function extractCode(string $response): string
    {
        $code = str_replace(['```php', '```', '<?php'], '', $response);
        return "<?php\n\n" . trim($code);
    }
}