<?php

namespace Iberbanco\SDK\Enums;

class ClientType 
{
    public const PERSONAL_TYPE = 1;
    public const BUSINESS_TYPE = 2;

    public const PERSONAL_TYPE_VALUE = 'personal';
    public const BUSINESS_TYPE_VALUE = 'business';

    public const VALUES = [
        self::PERSONAL_TYPE => self::PERSONAL_TYPE_VALUE,
        self::BUSINESS_TYPE => self::BUSINESS_TYPE_VALUE,
    ];

    public const ID_BY_VALUE = [
        self::PERSONAL_TYPE_VALUE => self::PERSONAL_TYPE,
        self::BUSINESS_TYPE_VALUE => self::BUSINESS_TYPE,
    ];

    public static function getTypeName(int $type): ?string
    {
        return self::VALUES[$type] ?? null;
    }

    public static function getTypeId(string $typeName): ?int
    {
        return self::ID_BY_VALUE[$typeName] ?? null;
    }

    public static function isValid(int $type): bool
    {
        return isset(self::VALUES[$type]);
    }

    public static function isPersonal(int $type): bool
    {
        return $type === self::PERSONAL_TYPE;
    }

    public static function isBusiness(int $type): bool
    {
        return $type === self::BUSINESS_TYPE;
    }
} 