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

    public function teamRoles(): array {
        return [
            self::Owner,
            self::Admin,
            self::Member
        ];
    }

    public function projectRoles(): array {
        return [
            self::Manager,
            self::User,
            self::Viewer
        ];
    }
}
