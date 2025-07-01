<?php

namespace Iberbanco\SDK\Enums;

class TransactionStatus 
{
    public const STATUS_NEW = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_DENIED = 3;
    public const STATUS_PROCESS = 4;
    public const STATUS_CANCELING = 5;
    public const STATUS_CANCELED = 6;
    public const STATUS_REFUNDED = 7;
    public const STATUS_ERROR = 8;

    public const VALUES = [
        self::STATUS_NEW => 'New',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_DENIED => 'Denied',
        self::STATUS_PROCESS => 'Processing',
        self::STATUS_CANCELING => 'Canceling',
        self::STATUS_CANCELED => 'Canceled',
        self::STATUS_REFUNDED => 'Refunded',
        self::STATUS_ERROR => 'Error',
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

    public static function isPending(int $status): bool
    {
        return in_array($status, [self::STATUS_NEW, self::STATUS_PROCESS]);
    }

    public static function isFinal(int $status): bool
    {
        return in_array($status, [
            self::STATUS_APPROVED, 
            self::STATUS_DENIED, 
            self::STATUS_CANCELED, 
            self::STATUS_REFUNDED, 
            self::STATUS_ERROR
        ]);
    }
} 