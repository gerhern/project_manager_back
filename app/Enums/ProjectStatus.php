<?php

namespace App\Enums;

enum ProjectStatus
{
    case Active;
    case CancelInProgress;
    case Canceled;
    case Completed;

    public function code(): string
    {
        return match ($this) {
            self::Active => 'AC',
            self::CancelInProgress => 'CP',
            self::Canceled => 'CA',
            self::Completed => 'CO',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Active => 'The project is currently active and ongoing.',
            self::CancelInProgress => 'The project is in the process of being canceled.',
            self::Canceled => 'The project has been canceled and is no longer active.',
            self::Completed => 'The project has been completed successfully.',
        };
    }

    public function isRestricted(): bool
    {
        return match ($this) {
            self::CancelInProgress,
            self::Canceled,
            self::Completed => true,
            default => false,
        };
    }
}
