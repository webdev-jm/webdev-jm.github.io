<?php

namespace App\Enums;

enum TicketCategory: string
{
    case Bug = 'bug';
    case FeatureRequest = 'feature_request';
    case Support = 'support';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Bug => 'Bug',
            self::FeatureRequest => 'Feature Request',
            self::Support => 'Support',
            self::General => 'General',
        };
    }
}
