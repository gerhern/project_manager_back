<?php

namespace App\enums;

enum TaskStatus
{
    case Pending;
    case Assigned;
    case InProgress;
    case Completed;
    case Canceled;

    public function code(): string
    {
        return match ($this) {
            self::Pending => 'PE',
            self::Assigned => 'AS',
            self::InProgress => 'IP',
            self::Completed => 'CO',
            self::Canceled => 'CA',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Pending => 'The task is pending and has not been started yet.',
            self::Assigned => 'The task has been assigned to a team member but work has not yet begun.',
            self::InProgress => 'The task is currently in progress.',
            self::Completed => 'The task has been completed successfully.',
            self::Canceled => 'The task has been canceled and will not be completed.',
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
