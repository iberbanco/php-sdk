<?php

namespace Iberbanco\SDK\Services;

use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

class CryptoTransactionService extends AbstractService
{
    public function list(array $filters = []): array
    {
        $queryParams = $this->buildCryptoTransactionListQuery($filters);
        return $this->get('crypto/transactions', $queryParams);
    }

    public function search(array $searchParams): array
    {
        $this->validateSearchParams($searchParams);
        return $this->post('crypto/transactions/search', $searchParams);
    }

    public function show(string $transactionNumber): array
    {
        if (empty(trim($transactionNumber))) {
            throw ValidationException::requiredField('transactionNumber');
        }

        return $this->get("crypto/transactions/{$transactionNumber}");
    }

    public function createPaymentLink(array $paymentLinkData): array
    {
        $this->validatePaymentLinkData($paymentLinkData);
        return $this->post('crypto/transactions/payment-link', $paymentLinkData);
    }

    private function buildCryptoTransactionListQuery(array $filters): array
    {
        $allowedFilters = [
            'per_page', 'page', 'status', 'type', 'cryptocurrency',
            'date_from', 'date_to', 'min_amount', 'max_amount',
            'reference', 'transaction_hash'
        ];

        $query = [];
        foreach ($allowedFilters as $filter) {
            if (isset($filters[$filter]) && $filters[$filter] !== null && $filters[$filter] !== '') {
                $query[$filter] = $filters[$filter];
            }
        }

        if (!isset($query['per_page'])) {
            $query['per_page'] = 50;
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

        if (isset($searchParams['cryptocurrency'])) {
            $this->validateCryptocurrency($searchParams['cryptocurrency']);
        }

        if (isset($searchParams['date_from']) || isset($searchParams['date_to'])) {
            $this->validateDateRange($searchParams);
        }

        if (isset($searchParams['min_amount']) || isset($searchParams['max_amount'])) {
            $this->validateAmountRange($searchParams);
        }
    }

    private function validatePaymentLinkData(array $paymentLinkData): void
    {
        $requiredFields = ['amount', 'currency'];
        $this->validateRequired($paymentLinkData, $requiredFields);

        if (isset($paymentLinkData['amount'])) {
            $amount = (float)$paymentLinkData['amount'];
            if ($amount <= 0) {
                throw ValidationException::minimumValue('amount', $amount, 0.01);
            }
        }

        if (isset($paymentLinkData['currency'])) {
            $this->validateCurrency($paymentLinkData['currency']);
        }

        if (isset($paymentLinkData['callback_url']) && !filter_var($paymentLinkData['callback_url'], FILTER_VALIDATE_URL)) {
            throw ValidationException::invalidFormat('callback_url', 'valid URL');
        }

        if (isset($paymentLinkData['return_url']) && !filter_var($paymentLinkData['return_url'], FILTER_VALIDATE_URL)) {
            throw ValidationException::invalidFormat('return_url', 'valid URL');
        }

        if (isset($paymentLinkData['expires_at'])) {
            try {
                $expiresAt = new \DateTime($paymentLinkData['expires_at']);
                $now = new \DateTime();
                
                if ($expiresAt <= $now) {
                    throw ValidationException::invalidValue('expires_at', $paymentLinkData['expires_at'], ['Must be in the future']);
                }
            } catch (\Exception $e) {
                throw ValidationException::invalidFormat('expires_at', 'valid datetime (Y-m-d H:i:s)');
            }
        }
    }

    private function validateCryptocurrency(string $cryptocurrency): void
    {
        $supportedCryptocurrencies = $this->getSupportedCryptocurrencies();
        
        $cryptocurrency = strtoupper($cryptocurrency);
        if (!in_array($cryptocurrency, array_keys($supportedCryptocurrencies))) {
            throw ValidationException::invalidValue('cryptocurrency', $cryptocurrency, array_keys($supportedCryptocurrencies));
        }
    }

    private function validateCurrency(string $currency): void
    {
        $supportedCurrencies = $this->getSupportedCurrencies();
        
        $currency = strtoupper($currency);
        if (!in_array($currency, array_keys($supportedCurrencies))) {
            throw ValidationException::invalidCurrency($currency);
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

    private function validateAmountRange(array $params): void
    {
        if (isset($params['min_amount'])) {
            $minAmount = (float)$params['min_amount'];
            if ($minAmount < 0) {
                throw ValidationException::minimumValue('min_amount', $minAmount, 0);
            }
        }

        if (isset($params['max_amount'])) {
            $maxAmount = (float)$params['max_amount'];
            if ($maxAmount < 0) {
                throw ValidationException::minimumValue('max_amount', $maxAmount, 0);
            }
        }

        if (isset($params['min_amount']) && isset($params['max_amount'])) {
            $minAmount = (float)$params['min_amount'];
            $maxAmount = (float)$params['max_amount'];
            
            if ($minAmount > $maxAmount) {
                throw ValidationException::invalidValue(
                    'amount_range',
                    "{$minAmount} - {$maxAmount}",
                    ['min_amount must be less than or equal to max_amount']
                );
            }
        }
    }

    public function getSupportedCryptocurrencies(): array
    {
        return [
            'BTC' => 'Bitcoin',
            'ETH' => 'Ethereum',
            'USDT' => 'Tether',
            'USDC' => 'USD Coin',
            'LTC' => 'Litecoin',
            'BCH' => 'Bitcoin Cash',
            'ADA' => 'Cardano',
            'DOT' => 'Polkadot',
            'LINK' => 'Chainlink',
            'XRP' => 'Ripple'
        ];
    }

    public function getSupportedCurrencies(): array
    {
        return [
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'CAD' => 'Canadian Dollar',
            'AUD' => 'Australian Dollar',
            'CHF' => 'Swiss Franc',
            'JPY' => 'Japanese Yen'
        ];
    }

    public function getSupportedTransactionTypes(): array
    {
        return [
            'DEPOSIT' => 'Deposit',
            'WITHDRAWAL' => 'Withdrawal',
            'EXCHANGE' => 'Exchange',
            'PAYMENT' => 'Payment',
            'REFUND' => 'Refund'
        ];
    }

    public function getSupportedStatuses(): array
    {
        return [
            'PENDING' => 'Pending',
            'CONFIRMED' => 'Confirmed',
            'COMPLETED' => 'Completed',
            'FAILED' => 'Failed',
            'CANCELLED' => 'Cancelled',
            'EXPIRED' => 'Expired'
        ];
    }
} 