<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class StarterSetup extends Command
{
    protected $signature = 'starter:setup
                            {--force : Overwrite existing .env without prompting}
                            {--skip-configure : Skip interactive .env value configuration}
                            {--skip-key : Skip key generation}
                            {--skip-symlink : Skip storage symlink creation}';

    protected $description = 'Automate .env configuration, key generation, and storage symlinking';

    public function handle(): int
    {
        $this->info('Laravel Starter Setup');
        $this->line('─────────────────────────────────────────');

        if (! $this->setupEnvFile()) {
            return self::FAILURE;
        }

        if (! $this->option('skip-configure')) {
            $this->configureEnvValues();
        }

        if (! $this->option('skip-key')) {
            $this->generateAppKey();
        }

        if (! $this->option('skip-symlink')) {
            $this->createStorageSymlink();
        }

        $this->line('');
        $this->info('Setup complete! Run `composer run dev` to start the development server.');

        return self::SUCCESS;
    }

    private function setupEnvFile(): bool
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (! file_exists($examplePath)) {
            $this->error('.env.example not found. Cannot proceed.');

            return false;
        }

        if (file_exists($envPath) && ! $this->option('force')) {
            if (! $this->confirm('.env already exists. Overwrite it?', false)) {
                $this->line('  Keeping existing .env file.');

                return true;
            }
        }

        copy($examplePath, $envPath);
        $this->line('  <fg=green>✓</> Copied .env.example → .env');

        return true;
    }

    private function configureEnvValues(): void
    {
        $this->line('');
        $this->comment('Configure environment values (press Enter to keep defaults):');
        $this->line('');

        $fields = [
            'APP_NAME' => ['label' => 'Application name', 'default' => 'Laravel Starter'],
            'APP_URL' => ['label' => 'Application URL', 'default' => 'http://localhost'],
            'DB_DATABASE' => ['label' => 'Database name', 'default' => 'laravel_startup_db'],
            'DB_USERNAME' => ['label' => 'Database username', 'default' => 'root'],
            'DB_PASSWORD' => ['label' => 'Database password', 'default' => '', 'secret' => true],
        ];

        foreach ($fields as $key => $config) {
            $current = $this->getEnvValue($key) ?? $config['default'];
            $label = $config['label'];

            $value = isset($config['secret']) && $config['secret']
                ? ($this->secret("  {$label} [hidden]") ?: $current)
                : ($this->ask("  {$label}", $current) ?? $current);

            if ($value !== $current) {
                $this->setEnvValue($key, $value);
            }
        }

        $this->line('');
        $this->line('  <fg=green>✓</> Environment values configured');
    }

    private function generateAppKey(): void
    {
        $this->callSilently('key:generate');
        $this->line('  <fg=green>✓</> Application key generated');
    }

    private function createStorageSymlink(): void
    {
        $publicStoragePath = public_path('storage');

        if (file_exists($publicStoragePath) || is_link($publicStoragePath)) {
            $this->line('  <fg=yellow>↷</> Storage symlink already exists, skipping');

            return;
        }

        $this->callSilently('storage:link');
        $this->line('  <fg=green>✓</> Storage symlink created');
    }

    private function getEnvValue(string $key): ?string
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return null;
        }

        $contents = file_get_contents($envPath);

        if (preg_match("/^{$key}=\"?([^\"\\n]*)\"?\$/m", $contents, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $contents = file_get_contents($envPath);

        $quoted = Str::contains($value, ' ') ? "\"{$value}\"" : $value;
        $pattern = "/^({$key}=).*\$/m";
        $replacement = "$1{$quoted}";

        $updated = preg_replace($pattern, $replacement, $contents);
        file_put_contents($envPath, $updated);
    }
}
