<?php

namespace Iberbanco\SDK\Services;

use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

class CardService extends AbstractService
{
    public function list(array $filters = []): array
    {
        $queryParams = $this->buildCardListQuery($filters);
        return $this->get('cards', $queryParams);
    }

    public function show(string $remoteId): array
    {
        if (empty(trim($remoteId))) {
            throw ValidationException::requiredField('remoteId');
        }

        return $this->get("cards/{$remoteId}");
    }

    public function create(array $cardData): array
    {
        $this->validateCardCreationData($cardData);
        return $this->post('cards/create', $cardData);
    }

    public function transactions(array $requestData): array
    {
        $this->validateCardTransactionsRequest($requestData);
        return $this->post('cards/transactions', $requestData);
    }

    public function requestPhysical(array $requestData): array
    {
        $this->validatePhysicalCardRequest($requestData);
        return $this->post('cards/request-physical', $requestData);
    }

    private function buildCardListQuery(array $filters): array
    {
        $allowedFilters = [
            'per_page', 'page', 'visibility', 'status', 'type',
            'user_number', 'currency', 'date_from', 'date_to'
        ];

        $query = [];
        foreach ($allowedFilters as $filter) {
            if (isset($filters[$filter]) && $filters[$filter] !== null && $filters[$filter] !== '') {
                $query[$filter] = $filters[$filter];
            }
        }

        if (!isset($query['per_page'])) {
            $query['per_page'] = 25;
        }

        if (isset($query['per_page'])) {
            $query['per_page'] = max(1, min((int)$query['per_page'], 100));
        }

        return $query;
    }

    private function validateCardCreationData(array $cardData): void
    {
        $requiredFields = ['user_number', 'account_number'];
        $this->validateRequired($cardData, $requiredFields);

        if (strlen($cardData['user_number']) < 6) {
            throw ValidationException::minimumValue('user_number', $cardData['user_number'], 6);
        }

        if (strlen($cardData['account_number']) < 10) {
            throw ValidationException::minimumValue('account_number', $cardData['account_number'], 10);
        }

        if (isset($cardData['card_type'])) {
            $this->validateCardType($cardData['card_type']);
        }

        if (isset($cardData['currency'])) {
            $this->validateCurrency($cardData['currency']);
        }

        if (isset($cardData['daily_limit']) && $cardData['daily_limit'] < 0) {
            throw ValidationException::minimumValue('daily_limit', $cardData['daily_limit'], 0);
        }

        if (isset($cardData['monthly_limit']) && $cardData['monthly_limit'] < 0) {
            throw ValidationException::minimumValue('monthly_limit', $cardData['monthly_limit'], 0);
        }

        if (isset($cardData['daily_limit']) && isset($cardData['monthly_limit'])) {
            if ($cardData['monthly_limit'] < $cardData['daily_limit']) {
                throw ValidationException::invalidValue(
                    'monthly_limit',
                    $cardData['monthly_limit'],
                    ['Monthly limit must be greater than or equal to daily limit']
                );
            }
        }
    }

    private function validateCardTransactionsRequest(array $requestData): void
    {
        $requiredFields = ['card_id'];
        $this->validateRequired($requestData, $requiredFields);

        if (isset($requestData['per_page'])) {
            $perPage = (int)$requestData['per_page'];
            if ($perPage < 1 || $perPage > 100) {
                throw ValidationException::invalidValue('per_page', $perPage, ['1-100']);
            }
        }

        if (isset($requestData['date_from']) || isset($requestData['date_to'])) {
            $this->validateDateRange($requestData);
        }
    }

    private function validatePhysicalCardRequest(array $requestData): void
    {
        $requiredFields = ['card_id', 'delivery_address'];
        $this->validateRequired($requestData, $requiredFields);

        if (isset($requestData['delivery_address'])) {
            $this->validateDeliveryAddress($requestData['delivery_address']);
        }

        if (isset($requestData['express_delivery']) && !is_bool($requestData['express_delivery'])) {
            throw ValidationException::invalidFormat('express_delivery', 'boolean');
        }
    }

    private function validateDeliveryAddress(array $address): void
    {
        $requiredFields = ['street', 'city', 'country', 'postal_code'];
        $this->validateRequired($address, $requiredFields);

        if (isset($address['country']) && strlen($address['country']) !== 2) {
            throw ValidationException::invalidFormat('country', 'ISO 3166-1 alpha-2 (2 letters)');
        }

        if (isset($address['postal_code']) && strlen($address['postal_code']) < 3) {
            throw ValidationException::minimumValue('postal_code', $address['postal_code'], 3);
        }

        if (isset($address['street']) && strlen($address['street']) < 5) {
            throw ValidationException::minimumValue('street', $address['street'], 5);
        }

        if (isset($address['city']) && strlen($address['city']) < 2) {
            throw ValidationException::minimumValue('city', $address['city'], 2);
        }
    }

    private function validateCardType(string $cardType): void
    {
        $supportedTypes = $this->getSupportedCardTypes();
        
        if (!in_array(strtolower($cardType), array_keys($supportedTypes))) {
            throw ValidationException::invalidValue('card_type', $cardType, array_keys($supportedTypes));
        }
    }

    private function validateCurrency($currency): void
    {
        $supportedCurrencies = $this->getSupportedCurrencies();
        
        if (is_numeric($currency)) {
            $currency = (int)$currency;
            if (!in_array($currency, array_keys($supportedCurrencies))) {
                throw ValidationException::invalidCurrency((string)$currency);
            }
        } elseif (is_string($currency)) {
            $currency = strtoupper($currency);
            if (!in_array($currency, array_values($supportedCurrencies))) {
                throw ValidationException::invalidCurrency($currency);
            }
        } else {
            throw ValidationException::invalidCurrency((string)$currency);
        }
    }

    private function validateDateRange(array $params): void
    {
        if (isset($params['date_from'])) {
            try {
                $this->formatDate($params['date_from']);
            } catch (\Exception $e) {
                throw ValidationException::invalidFormat('date_from', 'Y-m-d');
            }
        }

        if (isset($params['date_to'])) {
            try {
                $this->formatDate($params['date_to']);
            } catch (\Exception $e) {
                throw ValidationException::invalidFormat('date_to', 'Y-m-d');
            }
        }

        if (isset($params['date_from']) && isset($params['date_to'])) {
            $dateFrom = new \DateTime($params['date_from']);
            $dateTo = new \DateTime($params['date_to']);
            
            if ($dateFrom > $dateTo) {
                throw ValidationException::invalidValue(
                    'date_range', 
                    $params['date_from'] . ' - ' . $params['date_to'],
                    ['date_from must be before date_to']
                );
            }
        }
    }

    public function getSupportedCardTypes(): array
    {
        return [
            'virtual' => 'Virtual Card',
            'physical' => 'Physical Card'
        ];
    }

    public function getSupportedCurrencies(): array
    {
        return [
            840 => 'USD', // US Dollar
            978 => 'EUR', // Euro
            826 => 'GBP', // British Pound
            756 => 'CHF', // Swiss Franc
            124 => 'CAD', // Canadian Dollar
            036 => 'AUD', // Australian Dollar
        ];
    }

    public function getSupportedStatuses(): array
    {
        return [
            'ACTIVE' => 'Active',
            'INACTIVE' => 'Inactive',
            'BLOCKED' => 'Blocked',
            'EXPIRED' => 'Expired',
            'PENDING' => 'Pending'
        ];
    }

    public function getSupportedVisibilityOptions(): array
    {
        return [
            'active' => 'Active Cards',
            'inactive' => 'Inactive Cards',
            'all' => 'All Cards'
        ];
    }
} 