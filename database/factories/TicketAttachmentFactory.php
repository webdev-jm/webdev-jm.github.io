<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketAttachment>
 */
class TicketAttachmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'original_name' => fake()->word().'.pdf',
            'path' => 'ticket-attachments/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 512000),
        ];
    }
}
