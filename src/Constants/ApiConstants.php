<?php

namespace Iberbanco\SDK\Constants;

class ApiConstants
{
    public const API_VERSION = '1.0.0';

    public const DEFAULT_TIMEOUT = 30;

    public const MAX_TIMEOUT = 300;

    public const DEFAULT_PER_PAGE = 50;

    public const MAX_PER_PAGE = 100;

    public const MIN_PER_PAGE = 1;

    public const VALIDATION_LIMITS = [
        'name_min_length' => 2,
        'name_max_length' => 50,
        'email_max_length' => 255,
        'address_min_length' => 5,
        'address_max_length' => 255,
        'city_min_length' => 2,
        'city_max_length' => 100,
        'state_min_length' => 2,
        'state_max_length' => 100,
        'postal_code_min_length' => 3,
        'postal_code_max_length' => 10,
        'account_number_min_length' => 10,
        'user_number_min_length' => 6,
        'user_number_max_length' => 60,
        'password_min_length' => 6,
        'identity_document_min_length' => 5,
        'identity_document_max_length' => 50,
        'company_name_min_length' => 2,
        'company_registration_min_length' => 3,
        'card_remote_id_max_length' => 50,
        'order_id_max_length' => 255,
        'reference_max_length' => 255,
        'url_max_length' => 2048,
    ];

    public const AMOUNT_LIMITS = [
        'min_amount' => 0.01,
        'card_min_amount' => 1.0,
        'card_max_amount' => 5000.0,
        'crypto_min_amount' => 1.0,
        'max_exchange_amount' => 10000000.0, // 10 million
    ];

    public const DATE_LIMITS = [
        'min_age' => 18,
        'max_export_date_range_days' => 365,
        'timestamp_tolerance_seconds' => 300, // 5 minutes
        'max_historical_rate_days' => 365,
    ];

    public const REGEX_PATTERNS = [
        'name' => '/^[a-zA-Z\s\-\'\.]+$/',
        'alphanumeric' => '/^[a-zA-Z0-9]+$/',
        'phone_international' => '/^[+]?[1-9]\d{1,14}$/',
        'routing_number' => '/^\d{9}$/',
        'swift_code' => '/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
        'iban_format' => '/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/',
        'postal_code' => '/^[a-zA-Z0-9\s\-]{3,10}$/',
        'identity_document' => '/^[a-zA-Z0-9\-\s]+$/',
        'city_name' => '/^[a-zA-Z\s\-\'\.]+$/',
        'country_code' => '/^[A-Z]{2}$/',
        'card_year_range' => [2020, null], // null means current year + 1
        'card_month_range' => [1, 12],
    ];

    public const HTTP_STATUS = [
        'OK' => 200,
        'CREATED' => 201,
        'BAD_REQUEST' => 400,
        'UNAUTHORIZED' => 401,
        'FORBIDDEN' => 403,
        'NOT_FOUND' => 404,
        'UNPROCESSABLE_ENTITY' => 422,
        'TOO_MANY_REQUESTS' => 429,
        'INTERNAL_SERVER_ERROR' => 500,
        'SERVICE_UNAVAILABLE' => 503,
    ];

    public const USER_TYPES = [
        'PERSONAL' => 1,
        'BUSINESS' => 2,
    ];

    public const CARD_CONSTANTS = [
        'types' => ['virtual', 'physical'],
        'currencies' => [1 => 'USD', 2 => 'EUR'],
        'delivery_methods' => ['Standard', 'Registered'],
        'min_amount' => 1,
        'max_amount' => 5000,
    ];

    public const TRANSACTION_TYPES = [
        'SWIFT' => 'swift',
        'SEPA' => 'sepa', 
        'ACH' => 'ach',
        'BACS' => 'bacs',
        'CRYPTO' => 'crypto',
    ];

    public const EXPORT_FORMATS = ['csv', 'xlsx', 'json', 'xml'];

    public const CRYPTO_CURRENCIES = ['USD', 'EUR', 'GBP', 'CAD', 'TRY'];

    public const EXCHANGE_PRECISION = [
        'MIN' => 2,
        'MAX' => 8,
        'DEFAULT' => 4,
    ];

    public const CACHE_TTL = [
        'currencies' => 3600, // 1 hour
        'countries' => 86400, // 24 hours
        'exchange_rates' => 300, // 5 minutes
        'static_data' => 3600, // 1 hour
    ];

    public const RATE_LIMITS = [
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
        'burst_limit' => 10,
    ];

    public const FILE_LIMITS = [
        'max_size_bytes' => 8388608, // 8MB
        'allowed_types' => ['jpeg', 'png', 'jpg', 'pdf'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png', 
            'image/jpg',
            'application/pdf'
        ],
    ];

    public const ENDPOINTS = [
        'auth' => 'auth/login',
        'users_list' => 'users',
        'users_register_personal' => 'users/register/personal',
        'users_register_business' => 'users/register/business',
        'accounts_list' => 'accounts',
        'accounts_create' => 'accounts',
        'accounts_search' => 'accounts/search',
        'accounts_total_balance' => 'accounts/total-balance',
        'transactions_list' => 'transactions',
        'transactions_create' => 'transactions',
        'transactions_search' => 'transactions/search',
        'cards_list' => 'cards',
        'cards_create' => 'cards',
        'cards_transactions' => 'cards/transactions',
        'cards_request_physical' => 'cards/request-physical',
        'crypto_list' => 'crypto-transactions',
        'crypto_payment_link' => 'crypto-transactions/payment-link',
        'exchange_rate' => 'exchange/rate',
        'export_users' => 'export/users',
        'export_accounts' => 'export/accounts',
        'export_transactions' => 'export/transactions',
        'export_cards' => 'export/cards',
    ];

    public static function getValidationLimit(string $key)
    {
        return self::VALIDATION_LIMITS[$key] ?? null;
    }

    public static function getAmountLimit(string $key)
    {
        return self::AMOUNT_LIMITS[$key] ?? null;
    }

    public static function getRegexPattern(string $key)
    {
        return self::REGEX_PATTERNS[$key] ?? null;
    }

    public static function getCurrentYear(): int
    {
        return (int)date('Y');
    }

    public static function getMaxCardYear(): int
    {
        return self::getCurrentYear() + 1;
    }

    public static function isDevelopmentEnvironment(string $userAgent = ''): bool
    {
        $devPatterns = ['localhost', '127.0.0.1', 'dev', 'test', 'staging'];
        
        foreach ($devPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
} 