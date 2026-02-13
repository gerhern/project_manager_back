<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case Open = 'Open';
    case Accepted = 'Accepted';
    case Rejected = 'Rejected';
    case Expired = 'Expired';

    public function code(): string
    {
        return match ($this) {
            self::Open => 'OP',
            self::Accepted => 'AC',
            self::Rejected => 'RE',
            self::Expired => 'EX',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Open => 'The dispute is currently open and under review.',
            self::Accepted => 'The dispute has been accepted after review.',
            self::Rejected => 'The dispute has been rejected after review.',
            self::Expired => 'The dispute period has expired without resolution.',
        };
    }

    public static function resolutionStates(): array {
        return [
            self::Accepted,
            self::Rejected
        ];
    }
}
