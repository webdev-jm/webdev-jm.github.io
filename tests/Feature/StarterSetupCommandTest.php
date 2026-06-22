<?php

use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->originalEnv = file_get_contents(base_path('.env'));
});

afterEach(function () {
    file_put_contents(base_path('.env'), $this->originalEnv);
});

it('copies .env.example to .env with --force flag', function () {
    $this->artisan('starter:setup', ['--force' => true, '--skip-configure' => true, '--skip-key' => true, '--skip-symlink' => true])
        ->expectsOutputToContain('.env.example')
        ->assertExitCode(0);
});

it('skips .env copy when user declines overwrite', function () {
    $this->artisan('starter:setup', ['--skip-configure' => true, '--skip-key' => true, '--skip-symlink' => true])
        ->expectsConfirmation('.env already exists. Overwrite it?', 'no')
        ->expectsOutputToContain('Keeping existing .env')
        ->assertExitCode(0);
});

it('prompts for env values and writes changes', function () {
    $this->artisan('starter:setup', ['--force' => true, '--skip-key' => true, '--skip-symlink' => true])
        ->expectsQuestion('  Application name', 'My App')
        ->expectsQuestion('  Application URL', 'http://localhost')
        ->expectsQuestion('  Database name', 'laravel_startup_db')
        ->expectsQuestion('  Database username', 'root')
        ->expectsQuestion('  Database password [hidden]', '')
        ->expectsOutputToContain('Environment values configured')
        ->assertExitCode(0);
});

it('generates the application key', function () {
    $this->artisan('starter:setup', ['--force' => true, '--skip-configure' => true, '--skip-symlink' => true])
        ->expectsOutputToContain('Application key generated')
        ->assertExitCode(0);

    expect(config('app.key'))->not->toBeEmpty();
});

it('reports symlink already exists when public/storage is present', function () {
    $this->artisan('starter:setup', ['--force' => true, '--skip-configure' => true, '--skip-key' => true])
        ->expectsOutputToContain('symlink')
        ->assertExitCode(0);
});

it('prints setup complete message', function () {
    $this->artisan('starter:setup', ['--force' => true, '--skip-configure' => true, '--skip-key' => true, '--skip-symlink' => true])
        ->expectsOutputToContain('Setup complete')
        ->assertExitCode(0);
});
