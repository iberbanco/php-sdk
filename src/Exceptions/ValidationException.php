<?php

namespace Iberbanco\SDK\Exceptions;

class ValidationException extends IberbancoException
{
    public static function fieldValidation(array $errors, string $message = 'Validation failed'): self
    {
        return new self(
            $message,
            422,
            $errors
        );
    }

    public static function requiredField(string $field): self
    {
        return new self(
            "The {$field} field is required",
            422,
            ["The {$field} field is required"]
        );
    }

    public static function invalidFormat(string $field, string $expectedFormat): self
    {
        return new self(
            "The {$field} field must be in {$expectedFormat} format",
            422,
            ["The {$field} field must be in {$expectedFormat} format"]
        );
    }

    public static function invalidValue(string $field, $value, array $allowedValues = []): self
    {
        $message = "Invalid value '{$value}' for field '{$field}'";
        
        if (!empty($allowedValues)) {
            $message .= ". Allowed values: " . implode(', ', $allowedValues);
        }
        
        return new self(
            $message,
            422,
            [$message]
        );
    }

    public static function minimumValue(string $field, $value, $minimum): self
    {
        return new self(
            "The {$field} field must be at least {$minimum}. Got: {$value}",
            422,
            ["The {$field} field must be at least {$minimum}"]
        );
    }

    public static function maximumValue(string $field, $value, $maximum): self
    {
        return new self(
            "The {$field} field must not exceed {$maximum}. Got: {$value}",
            422,
            ["The {$field} field must not exceed {$maximum}"]
        );
    }

    public static function invalidCurrency(string $currency): self
    {
        return new self(
            "Invalid currency code: {$currency}",
            422,
            ["Invalid currency code: {$currency}"]
        );
    }

    public static function invalidEmail(string $email): self
    {
        return new self(
            "Invalid email format: {$email}",
            422,
            ["Invalid email format: {$email}"]
        );
    }

    public static function insufficientFunds(float $balance, float $amount): self
    {
        return new self(
            "Insufficient funds. Available: {$balance}, Required: {$amount}",
            422,
            ["Insufficient funds. Available: {$balance}, Required: {$amount}"]
        );
    }

    public static function range(string $field, $value, $min, $max): self
    {
        return new self(
            "The {$field} field must be between {$min} and {$max}. Got: {$value}",
            422,
            ["The {$field} field must be between {$min} and {$max}"]
        );
    }
} 