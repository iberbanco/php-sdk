<?php

namespace Iberbanco\SDK\Enums;

class CardStatus 
{
    public const STATUS_NEW = -1;
    public const STATUS_ISSUED = 0;
    public const STATUS_ACTIVATED = 1;
    public const STATUS_HOLD = 2;
    public const STATUS_SUSPEND = 3;
    public const STATUS_NORMAL = 4;
    public const STATUS_INACTIVE = 5;
    public const STATUS_LOST = 6;
    public const STATUS_STOLEN = 7;
    public const STATUS_EXPIRED = 8;
    public const STATUS_DENIED = 9;

    public const VALUES = [
        self::STATUS_NEW => 'New',
        self::STATUS_ISSUED => 'Issued',
        self::STATUS_ACTIVATED => 'Activated',
        self::STATUS_HOLD => 'Hold',
        self::STATUS_SUSPEND => 'Suspend',
        self::STATUS_NORMAL => 'Normal',
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_LOST => 'Lost',
        self::STATUS_STOLEN => 'Stolen',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_DENIED => 'Denied',
    ];

    public const ID_BY_VALUES = [
        'New' => self::STATUS_NEW,
        'Issued' => self::STATUS_ISSUED,
        'Activated' => self::STATUS_ACTIVATED,
        'Hold' => self::STATUS_HOLD,
        'Suspend' => self::STATUS_SUSPEND,
        'Normal' => self::STATUS_NORMAL,
        'Inactive' => self::STATUS_INACTIVE,
        'Lost' => self::STATUS_LOST,
        'Stolen' => self::STATUS_STOLEN,
        'Expired' => self::STATUS_EXPIRED,
        'Denied' => self::STATUS_DENIED,
    ];

    public static function getStatusName(int $status): ?string
    {
        return self::VALUES[$status] ?? null;
    }

    public static function getStatusId(string $statusName): ?int
    {
        return self::ID_BY_VALUES[$statusName] ?? null;
    }

    public static function isValid(int $status): bool
    {
        return isset(self::VALUES[$status]);
    }

    public static function isActive(int $status): bool
    {
        return in_array($status, [self::STATUS_ACTIVATED, self::STATUS_NORMAL]);
    }

    public static function isBlocked(int $status): bool
    {
        return in_array($status, [
            self::STATUS_HOLD, 
            self::STATUS_SUSPEND, 
            self::STATUS_LOST, 
            self::STATUS_STOLEN,
            self::STATUS_EXPIRED,
            self::STATUS_DENIED
        ]);
    }
} 