<?php

namespace Iberbanco\SDK\DTOs\Account;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;

class SearchAccountsDTO extends BaseDTO
{
    public ?string $user_number = null;
    public ?int $currency = null;
    public ?int $status = null;
    public ?string $account_number = null;
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?string $sort_by = null;
    public ?string $sort_order = null;
    public ?int $per_page = null;
    public ?int $page = null;

    public function validate(): void
    {
        if ($this->user_number && strlen($this->user_number) > 255) {
            throw ValidationException::maximumValue('user_number', strlen($this->user_number), 255);
        }

        if ($this->currency !== null && !is_int($this->currency)) {
            throw ValidationException::invalidFormat('currency', 'integer');
        }

        if ($this->status !== null && !is_int($this->status)) {
            throw ValidationException::invalidFormat('status', 'integer');
        }

        if ($this->account_number && strlen($this->account_number) > 255) {
            throw ValidationException::maximumValue('account_number', strlen($this->account_number), 255);
        }

        if ($this->date_from) {
            $this->validateDate($this->date_from, 'date_from');
        }

        if ($this->date_to) {
            $this->validateDate($this->date_to, 'date_to');
        }

        if ($this->date_from && $this->date_to) {
            $dateFrom = new \DateTime($this->date_from);
            $dateTo = new \DateTime($this->date_to);
            
            if ($dateFrom > $dateTo) {
                throw ValidationException::invalidValue(
                    'date_range',
                    $this->date_from . ' - ' . $this->date_to,
                    ['date_from must be before date_to']
                );
            }
        }

        if ($this->sort_by && !in_array($this->sort_by, ['id', 'account_special_number', 'currency', 'status', 'balance', 'created_at', 'updated_at'])) {
            throw ValidationException::invalidValue('sort_by', $this->sort_by, ['id', 'account_special_number', 'currency', 'status', 'balance', 'created_at', 'updated_at']);
        }

        if ($this->sort_order && !in_array($this->sort_order, ['asc', 'desc'])) {
            throw ValidationException::invalidValue('sort_order', $this->sort_order, ['asc', 'desc']);
        }

        if ($this->per_page !== null && ($this->per_page < 1 || $this->per_page > 100)) {
            throw ValidationException::invalidValue('per_page', $this->per_page, ['1-100']);
        }

        if ($this->page !== null && $this->page < 1) {
            throw ValidationException::minimumValue('page', $this->page, 1);
        }
    }

    public function getRequiredFields(): array
    {
        return []; // No required fields for searching
    }
} 