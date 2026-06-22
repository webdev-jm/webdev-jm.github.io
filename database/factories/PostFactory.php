<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'uuid'              => (string) Str::uuid(),
            'title'             => fake()->sentence(4),
            'body'              => fake()->paragraph(),
            'client_created_at' => now()->getTimestampMs(),
        ];
    }
}
