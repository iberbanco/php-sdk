<?php

namespace Iberbanco\SDK\Enums;

class Currency
{
    public const USD = 1;
    public const EUR = 2;
    public const GBP = 3;
    public const CHF = 4;
    public const RUB = 5;
    public const TRY = 6;
    public const AED = 7;
    public const CNH = 8;
    public const AUD = 9;
    public const CZK = 10;
    public const PLN = 11;
    public const CAD = 12;
    public const USDT = 13;
    public const HKD = 14;
    public const SGD = 15;
    public const JPY = 16;

    public const VALUES = [
        self::USD => 'USD',
        self::EUR => 'EUR',
        self::GBP => 'GBP',
        self::CHF => 'CHF',
        self::RUB => 'RUB',
        self::TRY => 'TRY',
        self::AED => 'AED',
        self::CNH => 'CNH',
        self::AUD => 'AUD',
        self::CZK => 'CZK',
        self::PLN => 'PLN',
        self::CAD => 'CAD',
        self::USDT => 'USDT',
        self::HKD => 'HKD',
        self::SGD => 'SGD',
        self::JPY => 'JPY',
    ];

    public const ID_BY_VALUES = [
        'USD' => self::USD,
        'EUR' => self::EUR,
        'GBP' => self::GBP,
        'CHF' => self::CHF,
        'RUB' => self::RUB,
        'TRY' => self::TRY,
        'AED' => self::AED,
        'CNH' => self::CNH,
        'AUD' => self::AUD,
        'CZK' => self::CZK,
        'PLN' => self::PLN,
        'CAD' => self::CAD,
        'USDT' => self::USDT,
        'HKD' => self::HKD,
        'SGD' => self::SGD,
        'JPY' => self::JPY,
    ];

    public const ISO_CODES = [
        'USD' => '840',
        'EUR' => '978',
        'GBP' => '826',
        'CHF' => '756',
        'RUB' => '643',
        'TRY' => '949',
        'AED' => '784',
        'CNH' => '156',
        'AUD' => '036',
        'CZK' => '203',
        'PLN' => '985',
        'CAD' => '124',
        'USDT' => '841',
        'HKD' => '344',
        'SGD' => '702',
        'JPY' => '392',
    ];

    public static function getIsoCode(string $currency): ?string
    {
        return self::ISO_CODES[$currency] ?? null;
    }

    public static function getIdByCode(string $code): ?int
    {
        return self::ID_BY_VALUES[$code] ?? null;
    }

    public static function getCodeById(int $id): ?string
    {
        return self::VALUES[$id] ?? null;
    }

    public static function isSupported(string $code): bool
    {
        return isset(self::ID_BY_VALUES[$code]);
    }

    public static function getAllCodes(): array
    {
        return array_keys(self::ID_BY_VALUES);
    }
} 