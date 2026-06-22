<?php

namespace Database\Factories;

use App\Models\SyncRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SyncRequest>
 */
class SyncRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => (string) Str::uuid(),
            'method' => 'POST',
            'url' => '/api/test',
            'payload' => ['test' => true],
            'client_timestamp' => now()->timestamp * 1000,
            'status' => 'pending',
            'server_response' => null,
        ];
    }
}
