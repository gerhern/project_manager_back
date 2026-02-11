<?php

namespace App\Enums;

enum RoleList: string
{
    case Owner = 'Owner';
    case Admin = 'Admin';
    case Member = 'Member';
    case Manager = 'Manager';
    case User = 'User';
    case Viewer = 'Viewer';

    public static function teamRoles(): array {
        return [
            self::Owner,
            self::Admin,
            self::Member
        ];
    }

    public static function teamManagementTier(): array {
        return [
            self::Owner,
            self::Admin
        ];
    }

    public static function projectRoles(): array {
        return [
            self::Manager,
            self::User,
            self::Viewer
        ];
    }

     public static function projectManagementTier(): array {
        return [
            self::Manager,
        ];
    }
}
