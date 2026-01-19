<?php

namespace App\Enums;

enum TeamStatus
{
    case Active;
    case Inactive;

    public function code(): string {
        return match ($this) {
            self::Active => 'Ac',
            self::Inactive => 'In'
        };
    }

    public function description(): string {
        return match($this){
            self::Active => 'The team is active',
            self::Inactive => 'The team is inactive'
        };
    }

    public function isRestricted(): bool {
        return match($this){
            self::Inactive => true,
            default => false,
        };
    }
}
