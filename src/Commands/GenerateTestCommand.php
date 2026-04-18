<?php

namespace Niduranga\DevGuard\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Niduranga\DevGuard\Services\GeminiService;

class GenerateTestCommand extends Command
{
    protected $signature = 'guard:test {class}';
    protected $description = 'Generate a mirrored unit test for any class using Gemini';

    public function handle(GeminiService $gemini)
    {
        $className = $this->argument('class');

        $relativeClassPath = str_replace(['App\\', '\\'], ['', '/'], $className);
        $sourcePath = app_path($relativeClassPath . '.php');

        if (!File::exists($sourcePath)) {
            $this->error("❌ Class not found at: {$sourcePath}");
            return;
        }

        $this->info("🚀 Analyzing Logic: {$className}...");
        $content = File::get($sourcePath);
        $framework = $this->detectTestFramework();

        try {
            $this->warn("🤖 Consulting Gemini (this may take a moment)...");
            $testCode = $gemini->generateTest($content, $framework);

            $testRelativePath = str_replace(['App\\', '\\'], ['', '/'], $className) . 'Test';

            $testFullPath = base_path("tests/Unit/{$testRelativePath}.php");
            $testDirectory = dirname($testFullPath);

            if (!File::isDirectory($testDirectory)) {
                File::makeDirectory($testDirectory, 0755, true);
                $this->comment("📁 Created directory: {$testDirectory}");
            }

            File::put($testFullPath, $testCode);

            $this->info("✅ Success! Test generated at: tests/Unit/{$testRelativePath}.php");

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