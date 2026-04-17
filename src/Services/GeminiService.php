<?php

namespace Niduranga\DevGuard\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct()
    {
        $this->apiKey = config('dev-guard.api_key') ?? '';
    }

    public function generateTest(string $code, string $framework): string
    {
        $model = config('dev-guard.model');
        $prompt = $this->buildPrompt($code, $framework);

        $response = Http::post("{$this->baseUrl}{$model}:generateContent?key={$this->apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception("AI Request Failed: " . $response->body());
        }

        return $this->extractCode($response->json('candidates.0.content.parts.0.text'));
    }

    protected function buildPrompt(string $code, string $framework): string
    {
        return "As an expert Laravel Developer, generate a technical {$framework} test for the following Action class. 
                Focus on:
                - Mocking external dependencies.
                - Testing the successful flow.
                - Following SOLID principles.
                
                ONLY return the PHP code. No explanations, no markdown code blocks like ```php.
                
                Action Class Code:
                {$code}";
    }

    protected function extractCode(string $response): string
    {
        return str_replace(['```php', '```'], '', $response);
    }
}