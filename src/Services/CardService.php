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
        $requiredFields = [
            'user_number', 'account_number', 'amount', 'currency',
            'shipping_address', 'shipping_city', 'shipping_state',
            'shipping_country_code', 'shipping_post_code', 'delivery_method'
        ];
        $this->validateRequired($cardData, $requiredFields);

        // Validate user_number (max 50 chars, alphanumeric)
        if (strlen($cardData['user_number']) > 50) {
            throw ValidationException::maximumValue('user_number', strlen($cardData['user_number']), 50);
        }
        if (!preg_match('/^[a-zA-Z0-9]+$/', $cardData['user_number'])) {
            throw ValidationException::invalidFormat('user_number', 'alphanumeric characters only');
        }

        // Validate account_number (max 255 chars)
        if (strlen($cardData['account_number']) > 255) {
            throw ValidationException::maximumValue('account_number', strlen($cardData['account_number']), 255);
        }

        // Validate amount (1-5000)
        if ($cardData['amount'] < 1 || $cardData['amount'] > 5000) {
            throw ValidationException::range('amount', $cardData['amount'], 1, 5000);
        }

        // Validate currency (1=USD, 2=EUR)
        if (!in_array($cardData['currency'], [1, 2])) {
            throw ValidationException::invalidValue('currency', $cardData['currency'], [1, 2]);
        }

        // Validate shipping fields
        if (strlen($cardData['shipping_address']) > 255) {
            throw ValidationException::maximumValue('shipping_address', strlen($cardData['shipping_address']), 255);
        }
        if (strlen($cardData['shipping_city']) > 100) {
            throw ValidationException::maximumValue('shipping_city', strlen($cardData['shipping_city']), 100);
        }
        if (strlen($cardData['shipping_state']) > 100) {
            throw ValidationException::maximumValue('shipping_state', strlen($cardData['shipping_state']), 100);
        }
        if (strlen($cardData['shipping_country_code']) !== 2) {
            throw ValidationException::invalidFormat('shipping_country_code', '2-letter country code');
        }
        if (strlen($cardData['shipping_post_code']) > 20) {
            throw ValidationException::maximumValue('shipping_post_code', strlen($cardData['shipping_post_code']), 20);
        }

        // Validate delivery_method
        if (!in_array($cardData['delivery_method'], ['Standard', 'Registered'])) {
            throw ValidationException::invalidValue('delivery_method', $cardData['delivery_method'], ['Standard', 'Registered']);
        }
    }

    private function validateCardTransactionsRequest(array $requestData): void
    {
        $requiredFields = ['remote_id', 'userNumber', 'san', 'year', 'month'];
        $this->validateRequired($requestData, $requiredFields);

        // Validate remote_id (max 50 chars)
        if (strlen($requestData['remote_id']) > 50) {
            throw ValidationException::maximumValue('remote_id', strlen($requestData['remote_id']), 50);
        }

        // Validate userNumber (max 50 chars)
        if (strlen($requestData['userNumber']) > 50) {
            throw ValidationException::maximumValue('userNumber', strlen($requestData['userNumber']), 50);
        }

        // Validate san (max 50 chars)
        if (strlen($requestData['san']) > 50) {
            throw ValidationException::maximumValue('san', strlen($requestData['san']), 50);
        }

        // Validate year
        $currentYear = (int)date('Y');
        if ($requestData['year'] < 2020 || $requestData['year'] > ($currentYear + 1)) {
            throw ValidationException::range('year', $requestData['year'], 2020, $currentYear + 1);
        }

        // Validate month
        if ($requestData['month'] < 1 || $requestData['month'] > 12) {
            throw ValidationException::range('month', $requestData['month'], 1, 12);
        }
    }

    private function validatePhysicalCardRequest(array $requestData): void
    {
        $requiredFields = ['remote_id'];
        $this->validateRequired($requestData, $requiredFields);

        // Validate remote_id (max 50 chars)
        if (strlen($requestData['remote_id']) > 50) {
            throw ValidationException::maximumValue('remote_id', strlen($requestData['remote_id']), 50);
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
            1 => 'USD',  // US Dollar
            2 => 'EUR',  // Euro
            3 => 'GBP',  // British Pound
            4 => 'CHF',  // Swiss Franc
            5 => 'RUB',  // Russian Ruble
            6 => 'TRY',  // Turkish Lira
            7 => 'AED',  // UAE Dirham
            8 => 'CNH',  // Chinese Yuan (Offshore)
            9 => 'AUD',  // Australian Dollar
            10 => 'CZK', // Czech Koruna
            11 => 'PLN', // Polish Zloty
            12 => 'CAD', // Canadian Dollar
            13 => 'USDT', // Tether
            14 => 'HKD', // Hong Kong Dollar
            15 => 'SGD', // Singapore Dollar
            16 => 'JPY', // Japanese Yen
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