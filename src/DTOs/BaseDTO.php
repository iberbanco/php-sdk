<?php

namespace Iberbanco\SDK\DTOs;

use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

abstract class BaseDTO
{
    private static array $propertyCache = [];

    public static function fromArray(array $data): static
    {
        $dto = new static();
        
        $className = static::class;
        if (!isset(self::$propertyCache[$className])) {
            self::$propertyCache[$className] = $dto->getPublicProperties();
        }
        
        $properties = self::$propertyCache[$className];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $properties, true)) {
                $dto->$key = $value;
            }
        }
        
        $dto->validate();
        return $dto;
    }

    public function toArray(bool $includeNulls = false): array
    {
        $className = static::class;
        if (!isset(self::$propertyCache[$className])) {
            self::$propertyCache[$className] = $this->getPublicProperties();
        }
        
        $properties = self::$propertyCache[$className];
        $result = [];
        
        foreach ($properties as $property) {
            $value = $this->$property;
            
            if ($includeNulls || $value !== null) {
                $result[$property] = $value;
            }
        }
        
        return $result;
    }

    private function getPublicProperties(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        return array_map(fn($property) => $property->getName(), $properties);
    }

    abstract public function validate(): void;

    abstract public function getRequiredFields(): array;

    protected function validateRequired(array $fields): void
    {
        $data = $this->toArray(true); // Include nulls for validation
        ValidationUtils::validateRequired($data, $fields);
    }

    protected function validateEmail(string $email, string $fieldName = 'email'): void
    {
        ValidationUtils::validateEmail($email, $fieldName);
    }

    protected function validateCurrency($currency, string $fieldName = 'currency'): void
    {
        ValidationUtils::validateCurrency($currency, $fieldName);
    }

    protected function validateAmount(float $amount, ?float $minAmount = null, string $fieldName = 'amount'): void
    {
        $minAmount = $minAmount ?? \Iberbanco\SDK\Constants\ApiConstants::getAmountLimit('min_amount');
        ValidationUtils::validateAmount($amount, $minAmount, $fieldName);
    }

    protected function validateDate(string $date, string $fieldName = 'date', string $format = 'Y-m-d'): void
    {
        ValidationUtils::validateDate($date, $format, $fieldName);
    }
} 