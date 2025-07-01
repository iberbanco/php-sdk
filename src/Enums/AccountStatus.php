<?php

namespace Iberbanco\SDK\Enums;

class AccountStatus
{
    public const REQUESTED = 1;
    public const ACTIVE = 2;
    public const INACTIVE = 3;

    public const VALUES = [
        self::REQUESTED => 'Requested',
        self::ACTIVE => 'Active',
        self::INACTIVE => 'Inactive'
    ];

    public const RELATION_ACCOUNT_STATUS_VALUES = [
        self::ACTIVE => 'Active',
        self::INACTIVE => 'Inactive'
    ];

    public static function getStatusName(int $status): ?string
    {
        return self::VALUES[$status] ?? null;
    }

    public static function getStatusId(string $statusName): ?int
    {
        $flipped = array_flip(self::VALUES);
        return $flipped[$statusName] ?? null;
    }

    public static function isValid(int $status): bool
    {
        return isset(self::VALUES[$status]);
    }
} 