<?php

namespace Iberbanco\SDK\Services;

use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

class ExportService extends AbstractService
{
    public function users(array $exportParams = []): array
    {
        $this->validateExportParams($exportParams);
        return $this->post('export/users', $exportParams);
    }

    public function accounts(array $exportParams = []): array
    {
        $this->validateExportParams($exportParams);
        return $this->post('export/accounts', $exportParams);
    }

    public function transactions(array $exportParams = []): array
    {
        $this->validateExportParams($exportParams);
        return $this->post('export/transactions', $exportParams);
    }

    public function cards(array $exportParams = []): array
    {
        $this->validateExportParams($exportParams);
        return $this->post('export/cards', $exportParams);
    }

    private function validateExportParams(array $exportParams): void
    {
        if (isset($exportParams['format'])) {
            $this->validateExportFormat($exportParams['format']);
        }

        if (isset($exportParams['date_from']) || isset($exportParams['date_to'])) {
            $this->validateDateRange($exportParams);
        }

        if (isset($exportParams['limit'])) {
            $limit = (int)$exportParams['limit'];
            if ($limit < 1 || $limit > 100000) {
                throw ValidationException::invalidValue('limit', $limit, ['1-100000']);
            }
        }

        if (isset($exportParams['columns']) && !is_array($exportParams['columns'])) {
            throw ValidationException::invalidFormat('columns', 'array');
        }

        if (isset($exportParams['notify_email']) && !filter_var($exportParams['notify_email'], FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::invalidEmail($exportParams['notify_email']);
        }

        if (isset($exportParams['compressed']) && !is_bool($exportParams['compressed'])) {
            throw ValidationException::invalidFormat('compressed', 'boolean');
        }
    }

    private function validateExportFormat(string $format): void
    {
        $supportedFormats = $this->getSupportedExportFormats();
        
        if (!in_array(strtolower($format), array_keys($supportedFormats))) {
            throw ValidationException::invalidValue('format', $format, array_keys($supportedFormats));
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

            $maxRange = 365; // days
            $diff = $dateTo->diff($dateFrom)->days;
            
            if ($diff > $maxRange) {
                throw ValidationException::invalidValue(
                    'date_range',
                    $params['date_from'] . ' - ' . $params['date_to'],
                    ["Date range cannot exceed {$maxRange} days"]
                );
            }
        }
    }

    public function getSupportedExportFormats(): array
    {
        return [
            'csv' => 'Comma-Separated Values',
            'xlsx' => 'Microsoft Excel',
            'json' => 'JSON Format',
            'xml' => 'XML Format'
        ];
    }

    public function getAvailableUserColumns(): array
    {
        return [
            'user_number', 'email', 'first_name', 'last_name',
            'date_of_birth', 'country', 'citizenship', 'address',
            'city', 'post_code', 'call_number', 'status',
            'type', 'created_at', 'updated_at'
        ];
    }

    public function getAvailableAccountColumns(): array
    {
        return [
            'account_number', 'user_number', 'currency', 'balance',
            'status', 'iban', 'bic', 'account_name',
            'created_at', 'updated_at'
        ];
    }

    public function getAvailableTransactionColumns(): array
    {
        return [
            'transaction_number', 'account_number', 'amount', 'currency',
            'type', 'status', 'direction', 'reference', 'description',
            'recipient_name', 'recipient_account', 'created_at', 'completed_at'
        ];
    }

    public function getAvailableCardColumns(): array
    {
        return [
            'card_id', 'user_number', 'account_number', 'card_type',
            'status', 'currency', 'daily_limit', 'monthly_limit',
            'last_four', 'expiry_date', 'created_at', 'updated_at'
        ];
    }

    public function getExportStatuses(): array
    {
        return [
            'QUEUED' => 'Queued for Processing',
            'PROCESSING' => 'Processing',
            'COMPLETED' => 'Completed',
            'FAILED' => 'Failed',
            'EXPIRED' => 'Expired'
        ];
    }
} 