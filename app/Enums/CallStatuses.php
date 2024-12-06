<?php

namespace App\Enums;

enum CallStatuses: string
{
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in-progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public static function getValues(): array
    {
        return array_map(fn($status) => $status->value, self::cases());
    }
}
