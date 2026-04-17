<?php

use Illuminate\Support\Facades\Http;
use Niduranga\DevGuard\Services\GeminiService;

it('can extract code from gemini response and clean markdown', function () {
    config(['dev-guard.api_key' => 'fake-api-key']);
    config(['dev-guard.model' => 'gemini-1.5-flash']);

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => "```php\n<?php\n\nit('works', function () {\n    expect(true)->toBeTrue();\n});\n```"
                            ]
                        ]
                    ]
                ]
            ]
        ], 200),
    ]);

    $service = new GeminiService();
    $result = $service->generateTest('class MyAction {}', 'Pest');

    expect($result)->not->toContain('```php');
    expect($result)->not->toContain('```');
    expect($result)->toContain("it('works'");
});

it('throws an exception if the api request fails', function () {
    config(['dev-guard.api_key' => 'fake-api-key']);

    Http::fake([
        '[https://generativelanguage.googleapis.com/](https://generativelanguage.googleapis.com/)*' => Http::response(['error' => 'Invalid API Key'], 403),
    ]);

    $service = new GeminiService();

    expect(fn () => $service->generateTest('code', 'Pest'))
        ->toThrow(Exception::class);
});