<?php

namespace Niduranga\DevGuard\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'devguard:install';
    protected $description = 'Install and setup Laravel DevGuard';

    public function handle()
    {
        $this->info('🚀 Installing Laravel DevGuard...');

        // 1. Publish Configuration
        $this->comment('Publishing configuration...');
        $this->call('vendor:publish', [
            '--provider' => "Niduranga\DevGuard\DevGuardServiceProvider",
            '--tag' => "config"
        ]);

        // 2. Check .env for API Key
        $this->checkEnvFile();

        $this->info('✅ Installation complete! Happy coding, Niduranga!');
    }

    protected function checkEnvFile()
    {
        $envPath = base_path('.env');

        if (File::exists($envPath)) {
            $content = File::get($envPath);

            if (!str_contains($content, 'GEMINI_API_KEY')) {
                $this->warn('⚠️  GEMINI_API_KEY not found in your .env file.');
                $this->line('Please add: GEMINI_API_KEY=your_key_here');
            } else {
                $this->info('✅ GEMINI_API_KEY detected in .env.');
            }
        }
    }
}