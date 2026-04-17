<?php

namespace Niduranga\DevGuard\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Niduranga\DevGuard\Services\GeminiService;

class GenerateTestCommand extends Command
{
    protected $signature = 'guard:test {class}';
    protected $description = 'Generate a high-performance test for a given Action class using Gemini 2.5';

    public function handle(GeminiService $gemini)
    {
        $className = $this->argument('class');

        // Path logic for Laravel Actions
        $path = app_path(str_replace(['App\\', '\\'], ['', '/'], $className) . '.php');

        if (!File::exists($path)) {
            $this->error("❌ Action class not found at: {$path}");
            return;
        }

        $this->info("🚀 Analyzing Logic: {$className}...");
        $content = File::get($path);
        $framework = $this->detectTestFramework();

        try {
            $this->warn("🤖 Consulting Gemini 2.5 (this may take a moment)...");

            $testCode = $gemini->generateTest($content, $framework);

            $testClassName = class_basename($className) . 'Test';
            $testPath = base_path("tests/Feature/{$testClassName}.php");

            File::ensureDirectoryExists(base_path('tests/Feature'));
            File::put($testPath, $testCode);

            $this->info("✅ Success! Test generated at: tests/Feature/{$testClassName}.php");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }

    protected function detectTestFramework(): string
    {
        $composerPath = base_path('composer.json');
        if (File::exists($composerPath)) {
            $composer = json_decode(File::get($composerPath), true);
            if (isset($composer['require-dev']['pestphp/pest'])) {
                return 'Pest';
            }
        }
        return 'PHPUnit';
    }
}