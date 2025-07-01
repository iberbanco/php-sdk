<?php

namespace Iberbanco\SDK\Utils;

use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Enums\Currency;

class ValidationUtils
{
    public const CRYPTO_PAYMENT_CURRENCIES = ['USD', 'EUR', 'GBP', 'CAD', 'TRY'];

    public const IDENTITY_DOCUMENT_TYPES = ['passport', 'national_id', 'driving_license'];

    public const CARD_CURRENCIES = [Currency::USD => 'USD', Currency::EUR => 'EUR'];

    public const DELIVERY_METHODS = ['Standard', 'Registered'];

    public static function validateEmail(string $email, string $fieldName = 'email'): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::invalidEmail($email);
        }
    }

    public static function validateCurrency($currency, string $fieldName = 'currency'): void
    {
        if (is_numeric($currency)) {
            if (!in_array((int)$currency, array_keys(Currency::VALUES))) {
                throw ValidationException::invalidCurrency((string)$currency);
            }
        } elseif (is_string($currency)) {
            if (!Currency::isSupported(strtoupper($currency))) {
                throw ValidationException::invalidCurrency($currency);
            }
        } else {
            throw ValidationException::invalidCurrency((string)$currency);
        }
    }

    public static function validateIban(string $iban, string $fieldName = 'iban'): void
    {
        $iban = strtoupper(str_replace(' ', '', $iban));
        
        if (strlen($iban) < 15 || strlen($iban) > 34) {
            throw ValidationException::invalidFormat($fieldName, 'valid IBAN (15-34 characters)');
        }
        
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $iban)) {
            throw ValidationException::invalidFormat($fieldName, 'valid IBAN format');
        }
        
        if (!self::verifyIbanChecksum($iban)) {
            throw ValidationException::invalidFormat($fieldName, 'valid IBAN checksum');
        }
    }

    private static function verifyIbanChecksum(string $iban): bool
    {
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);
        
        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                $numeric .= (ord($char) - ord('A') + 10);
            } else {
                $numeric .= $char;
            }
        }
        
        return bcmod($numeric, '97') === '1';
    }

    public static function validateSwiftCode(string $swiftCode, string $fieldName = 'swift_code'): void
    {
        if (!preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', strtoupper($swiftCode))) {
            throw ValidationException::invalidFormat($fieldName, 'valid SWIFT/BIC code (8-11 characters)');
        }
    }

    public static function validateBic(string $bic, string $fieldName = 'bic'): void
    {
        self::validateSwiftCode($bic, $fieldName);
    }

    public static function validateRoutingNumber(string $routingNumber, string $fieldName = 'routing_number'): void
    {
        if (!preg_match('/^\d{9}$/', $routingNumber)) {
            throw ValidationException::invalidFormat($fieldName, '9-digit routing number');
        }
    }

    public static function validatePhoneNumber(string $phoneNumber, string $fieldName = 'phone'): void
    {
        if (!preg_match('/^[+]?[1-9]\d{1,14}$/', $phoneNumber)) {
            throw ValidationException::invalidFormat($fieldName, 'valid international phone number');
        }
    }

    public static function validateDateOfBirth(string $dateOfBirth, string $fieldName = 'date_of_birth'): void
    {
        $date = \DateTime::createFromFormat('Y-m-d', $dateOfBirth);
        
        if (!$date || $date->format('Y-m-d') !== $dateOfBirth) {
            throw ValidationException::invalidFormat($fieldName, 'Y-m-d');
        }

        $now = new \DateTime();
        $age = $now->diff($date)->y;

        if ($age < 18) {
            throw ValidationException::minimumValue('age', $age, 18);
        }

        if ($date > $now) {
            throw ValidationException::invalidValue($fieldName, $dateOfBirth, ['Date cannot be in the future']);
        }
    }

    public static function validateAddress(array $address, string $fieldPrefix = 'address'): void
    {
        $requiredFields = ['street', 'city', 'state', 'postal_code', 'country'];
        
        foreach ($requiredFields as $field) {
            if (!isset($address[$field]) || (is_string($address[$field]) && trim($address[$field]) === '')) {
                throw ValidationException::requiredField("{$fieldPrefix}.{$field}");
            }
        }

        if (isset($address['street'])) {
            self::validateLength($address['street'], 5, 255, "{$fieldPrefix}.street");
        }

        if (isset($address['city'])) {
            self::validateLength($address['city'], 2, 100, "{$fieldPrefix}.city");
            if (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $address['city'])) {
                throw ValidationException::invalidFormat("{$fieldPrefix}.city", 'letters, spaces, hyphens, apostrophes, and periods only');
            }
        }

        if (isset($address['state'])) {
            self::validateLength($address['state'], 2, 100, "{$fieldPrefix}.state");
        }

        if (isset($address['postal_code']) && !preg_match('/^[a-zA-Z0-9\s\-]{3,10}$/', $address['postal_code'])) {
            throw ValidationException::invalidFormat("{$fieldPrefix}.postal_code", 'alphanumeric with spaces and hyphens, 3-10 characters');
        }

        if (isset($address['country'])) {
            self::validateCountryCode($address['country'], "{$fieldPrefix}.country");
        }
    }

    public static function validateCountryCode(string $countryCode, string $fieldName = 'country'): void
    {
        if (strlen($countryCode) !== 2) {
            throw ValidationException::invalidFormat($fieldName, 'ISO 3166-1 alpha-2 (2 letters)');
        }
        
        if (!ctype_alpha($countryCode)) {
            throw ValidationException::invalidFormat($fieldName, 'alphabetic characters only');
        }
        
        if (strtoupper($countryCode) !== $countryCode) {
            throw ValidationException::invalidFormat($fieldName, 'uppercase letters');
        }
    }

    public static function validateLength(string $value, int $minLength, int $maxLength, string $fieldName): void
    {
        $length = strlen($value);
        
        if ($length < $minLength) {
            throw ValidationException::minimumValue($fieldName, $length, $minLength);
        }
        
        if ($length > $maxLength) {
            throw ValidationException::maximumValue($fieldName, $length, $maxLength);
        }
    }

    public static function validateNameFormat(string $name, string $fieldName): void
    {
        if (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $name)) {
            throw ValidationException::invalidFormat($fieldName, 'letters, spaces, hyphens, apostrophes, and periods only');
        }
    }

    public static function validateAlphanumeric(string $value, string $fieldName): void
    {
        if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            throw ValidationException::invalidFormat($fieldName, 'alphanumeric characters only');
        }
    }

    public static function validateIdentityDocumentNumber(string $documentNumber, string $fieldName = 'identity_document_number'): void
    {
        self::validateLength($documentNumber, 5, 50, $fieldName);
        
        if (!preg_match('/^[a-zA-Z0-9\-\s]+$/', $documentNumber)) {
            throw ValidationException::invalidFormat($fieldName, 'alphanumeric characters, hyphens, and spaces only');
        }
    }

    public static function validateUrl(string $url, int $maxLength = 2048, string $fieldName = 'url'): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw ValidationException::invalidFormat($fieldName, 'valid URL');
        }
        
        if (strlen($url) > $maxLength) {
            throw ValidationException::maximumValue($fieldName, strlen($url), $maxLength);
        }
    }

    public static function validateAmount(float $amount, float $minAmount = 0.01, string $fieldName = 'amount'): void
    {
        if ($amount < $minAmount) {
            throw ValidationException::minimumValue($fieldName, $amount, $minAmount);
        }
    }

    public static function validateDate(string $date, string $format = 'Y-m-d', string $fieldName = 'date'): void
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        
        if (!$dateTime || $dateTime->format($format) !== $date) {
            throw ValidationException::invalidFormat($fieldName, $format);
        }
    }

    public static function validateRequired(array $data, array $requiredFields): void
    {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw ValidationException::fieldValidation(
                array_map(fn($field) => "The {$field} field is required", $missing),
                'Missing required fields: ' . implode(', ', $missing)
            );
        }
    }
} 