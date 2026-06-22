<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Low => 'badge-secondary',
            self::Medium => 'badge-info',
            self::High => 'badge-warning',
            self::Urgent => 'badge-danger',
        };
    }
}
