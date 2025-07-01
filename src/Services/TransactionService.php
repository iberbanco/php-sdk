<?php

namespace Iberbanco\SDK\Services;

use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

class TransactionService extends AbstractService
{
    public function list(array $filters = []): array
    {
        $queryParams = $this->buildTransactionListQuery($filters);
        return $this->get('transactions', $queryParams);
    }

    public function search(array $searchParams): array
    {
        $this->validateSearchParams($searchParams);
        return $this->post('transactions/search', $searchParams);
    }

    public function show(string $transactionNumber): array
    {
        if (empty(trim($transactionNumber))) {
            throw ValidationException::requiredField('transactionNumber');
        }

        return $this->get("transactions/{$transactionNumber}");
    }

    public function create(string $type, array $transactionData): array
    {
        $this->validateTransactionType($type);
        $this->validateTransactionData($type, $transactionData);
        
        return $this->post("transactions/{$type}", $transactionData);
    }

    public function createSwift(array $transactionData): array
    {
        return $this->create('swift', $transactionData);
    }

    public function createSepa(array $transactionData): array
    {
        return $this->create('sepa', $transactionData);
    }

    public function createAch(array $transactionData): array
    {
        return $this->create('ach', $transactionData);
    }

    public function createBacs(array $transactionData): array
    {
        return $this->create('bacs', $transactionData);
    }

    private function buildTransactionListQuery(array $filters): array
    {
        $allowedFilters = [
            'per_page', 'page', 'status', 'type', 'account_number',
            'date_from', 'date_to', 'min_amount', 'max_amount',
            'direction', 'reference', 'recipient'
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

        if (isset($searchParams['date_from']) || isset($searchParams['date_to'])) {
            $this->validateDateRange($searchParams);
        }

        if (isset($searchParams['min_amount']) || isset($searchParams['max_amount'])) {
            $this->validateAmountRange($searchParams);
        }
    }

    private function validateTransactionType(string $type): void
    {
        $supportedTypes = $this->getSupportedTransactionTypes();
        
        if (!in_array(strtolower($type), array_keys($supportedTypes))) {
            throw ValidationException::invalidValue('type', $type, array_keys($supportedTypes));
        }
    }

    private function validateTransactionData(string $type, array $transactionData): void
    {
        $commonRequired = ['account_number', 'amount'];
        $this->validateRequired($transactionData, $commonRequired);

        if (isset($transactionData['amount'])) {
            $amount = (float)$transactionData['amount'];
            if ($amount <= 0) {
                throw ValidationException::minimumValue('amount', $amount, 0.01);
            }
        }

        switch (strtolower($type)) {
            case 'swift':
                $this->validateSwiftData($transactionData);
                break;
            case 'sepa':
                $this->validateSepaData($transactionData);
                break;
            case 'ach':
                $this->validateAchData($transactionData);
                break;
            case 'bacs':
                $this->validateBacsData($transactionData);
                break;
        }
    }

    private function validateSwiftData(array $data): void
    {
        $required = ['recipient_account_number', 'recipient_bank_code'];
        $this->validateRequired($data, $required);

        if (isset($data['recipient_bank_code']) && !preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $data['recipient_bank_code'])) {
            throw ValidationException::invalidFormat('recipient_bank_code', 'valid SWIFT/BIC code');
        }
    }

    private function validateSepaData(array $data): void
    {
        $required = ['recipient_iban', 'recipient_name'];
        $this->validateRequired($data, $required);

        if (isset($data['recipient_iban']) && !$this->validateIban($data['recipient_iban'])) {
            throw ValidationException::invalidFormat('recipient_iban', 'valid IBAN');
        }
    }

    private function validateAchData(array $data): void
    {
        $required = ['recipient_account_number', 'recipient_routing_number'];
        $this->validateRequired($data, $required);

        if (isset($data['recipient_routing_number']) && !preg_match('/^\d{9}$/', $data['recipient_routing_number'])) {
            throw ValidationException::invalidFormat('recipient_routing_number', '9-digit routing number');
        }
    }

    private function validateBacsData(array $data): void
    {
        $required = ['recipient_account_number', 'recipient_sort_code'];
        $this->validateRequired($data, $required);

        if (isset($data['recipient_sort_code']) && !preg_match('/^\d{6}$/', str_replace('-', '', $data['recipient_sort_code']))) {
            throw ValidationException::invalidFormat('recipient_sort_code', '6-digit sort code');
        }
    }

    private function validateIban(string $iban): bool
    {
        $iban = strtoupper(str_replace(' ', '', $iban));
        
        if (strlen($iban) < 15 || strlen($iban) > 34) {
            return false;
        }
        
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $iban)) {
            return false;
        }
        
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

    public function getSupportedTransactionTypes(): array
    {
        return [
            'swift' => 'SWIFT International Wire',
            'sepa' => 'SEPA Transfer',
            'ach' => 'ACH Transfer',
            'bacs' => 'BACS Transfer',
            'eft' => 'EFT Transfer',
            'interac' => 'INTERAC Transfer',
            'internal' => 'Internal Transfer',
            'pan-africa' => 'Pan Africa Transfer'
        ];
    }

    public function getSupportedStatuses(): array
    {
        return [
            'NEW' => 'New',
            'PENDING' => 'Pending',
            'PROCESSING' => 'Processing',
            'COMPLETED' => 'Completed',
            'FAILED' => 'Failed',
            'CANCELLED' => 'Cancelled'
        ];
    }

    public function getSupportedDirections(): array
    {
        return [
            'IN' => 'Incoming',
            'OUT' => 'Outgoing'
        ];
    }
} 