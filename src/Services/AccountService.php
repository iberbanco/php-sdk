<?php

namespace Iberbanco\SDK\Services;

use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

class AccountService extends AbstractService
{
    public function list(array $filters = []): array
    {
        $queryParams = $this->buildAccountListQuery($filters);
        return $this->get('accounts', $queryParams);
    }

    public function search(array $searchParams): array
    {
        $this->validateSearchParams($searchParams);
        return $this->post('accounts/search', $searchParams);
    }

    public function show(string $accountNumber): array
    {
        if (empty(trim($accountNumber))) {
            throw ValidationException::requiredField('accountNumber');
        }

        return $this->get("accounts/{$accountNumber}");
    }

    public function create(array $accountData): array
    {
        $this->validateAccountCreationData($accountData);
        return $this->post('accounts/create', $accountData);
    }

    public function totalBalance(array $params): array
    {
        $this->validateRequired($params, ['currency']);
        return $this->get('accounts/total-balance', $params);
    }

    private function buildAccountListQuery(array $filters): array
    {
        $allowedFilters = [
            'per_page', 'page', 'currency', 'status', 'user_number',
            'date_from', 'date_to', 'min_balance', 'max_balance'
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

    private function validateSearchParams(array $searchParams): void
    {
        if (isset($searchParams['per_page'])) {
            $perPage = (int)$searchParams['per_page'];
            if ($perPage < 1 || $perPage > 100) {
                throw ValidationException::invalidValue('per_page', $perPage, ['1-100']);
            }
        }

        if (isset($searchParams['currency'])) {
            $this->validateCurrency($searchParams['currency']);
        }

        if (isset($searchParams['date_from']) || isset($searchParams['date_to'])) {
            $this->validateDateRange($searchParams);
        }

        if (isset($searchParams['min_balance']) || isset($searchParams['max_balance'])) {
            $this->validateBalanceRange($searchParams);
        }
    }

    private function validateAccountCreationData(array $accountData): void
    {
        $requiredFields = ['user_number', 'currency'];
        $this->validateRequired($accountData, $requiredFields);

        if (strlen($accountData['user_number']) > 50) {
            throw ValidationException::maximumValue('user_number', strlen($accountData['user_number']), 50);
        }
        
        if (!preg_match('/^[a-zA-Z0-9]+$/', $accountData['user_number'])) {
            throw ValidationException::invalidFormat('user_number', 'alphanumeric characters only');
        }

        $currency = $accountData['currency'];
        if (is_array($currency)) {
            foreach ($currency as $curr) {
                $this->validateCurrency($curr);
            }
        } else {
            $this->validateCurrency($currency);
        }

        if (isset($accountData['reference']) && strlen($accountData['reference']) > 255) {
            throw ValidationException::maximumValue('reference', strlen($accountData['reference']), 255);
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

    private function validateBalanceRange(array $params): void
    {
        if (isset($params['min_balance'])) {
            $minBalance = (float)$params['min_balance'];
            if ($minBalance < 0) {
                throw ValidationException::minimumValue('min_balance', $minBalance, 0);
            }
        }

        if (isset($params['max_balance'])) {
            $maxBalance = (float)$params['max_balance'];
            if ($maxBalance < 0) {
                throw ValidationException::minimumValue('max_balance', $maxBalance, 0);
            }
        }

        if (isset($params['min_balance']) && isset($params['max_balance'])) {
            $minBalance = (float)$params['min_balance'];
            $maxBalance = (float)$params['max_balance'];
            
            if ($minBalance > $maxBalance) {
                throw ValidationException::invalidValue(
                    'balance_range',
                    "{$minBalance} - {$maxBalance}",
                    ['min_balance must be less than or equal to max_balance']
                );
            }
        }
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
            'REQUESTED' => 'Requested',
            'ACTIVE' => 'Active',
            'SUSPENDED' => 'Suspended',
            'CLOSED' => 'Closed'
        ];
    }
} 