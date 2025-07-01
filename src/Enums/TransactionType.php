<?php

namespace Iberbanco\SDK\Enums;

class TransactionType 
{
    public const INTRA_TRANSACTION = 1;
    public const INTERNATIONAL_TRANSACTION_SWIFT = 2;
    public const EXCHANGE_TRANSACTION = 3;
    public const ACH_TRANSACTION = 4;
    public const EFT_TRANSACTION = 5;
    public const SEPA_TRANSACTION = 6;
    public const BACS_TRANSACTION = 7;
    public const DOMESTIC_WIRE_TRANSACTION = 8;
    public const AZA_TRANSACTION = 9;
    public const CARD_TRANSACTION = 10;
    public const CRYPTO_TRANSACTION = 11;
    public const INTERAC_TRANSACTION = 12;
    public const BILL_PAYMENT_TRANSACTION = 13;

    public const VALUES = [
        self::INTRA_TRANSACTION => 'Intra Transaction',
        self::SEPA_TRANSACTION => 'Europe Transaction (SEPA)',
        self::INTERNATIONAL_TRANSACTION_SWIFT => 'International Transaction(SWIFT)',
        self::AZA_TRANSACTION => 'International Transaction (Pan Africa)',
        self::EXCHANGE_TRANSACTION => 'Exchange Transaction',
        self::ACH_TRANSACTION => 'ACH Transaction',
        self::EFT_TRANSACTION => 'EFT Transaction',
        self::BACS_TRANSACTION => 'BACS Transaction',
        self::DOMESTIC_WIRE_TRANSACTION => 'Domestic Wire Transaction',
        self::CARD_TRANSACTION => 'Card Transaction',
        self::CRYPTO_TRANSACTION => 'Crypto Transaction',
        self::INTERAC_TRANSACTION => 'INTERAC Transaction',
        self::BILL_PAYMENT_TRANSACTION => 'Bill Payment Transaction',
    ];

    public static function getTypeName(int $type): ?string
    {
        return self::VALUES[$type] ?? null;
    }

    public static function getTypeId(string $typeName): ?int
    {
        $flipped = array_flip(self::VALUES);
        return $flipped[$typeName] ?? null;
    }

    public static function isValid(int $type): bool
    {
        return isset(self::VALUES[$type]);
    }

    public static function requiresInternationalFields(int $type): bool
    {
        return in_array($type, [
            self::INTERNATIONAL_TRANSACTION_SWIFT,
            self::AZA_TRANSACTION
        ]);
    }

    public static function isCrypto(int $type): bool
    {
        return $type === self::CRYPTO_TRANSACTION;
    }

    public static function isCard(int $type): bool
    {
        return $type === self::CARD_TRANSACTION;
    }
} 