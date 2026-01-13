<?php

namespace App\enums;

enum ObjectiveStatus
{
    case Completed;
    case NotCompleted;
    case Canceled;

    public function code(): string
    {
        return match ($this) {
            self::Completed => 'CO',
            self::NotCompleted => 'NC',
            self::Canceled => 'CA',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Completed => 'The objective has been completed successfully.',
            self::NotCompleted => 'The objective has not been completed yet.',
            self::Canceled => 'The objective has been canceled and will not be completed.',
        };
    }

    public function isRestricted(): bool
    {
        return match ($this) {
            self::Completed,
            self::Canceled => true,
            default => false,
        };
    }
}
