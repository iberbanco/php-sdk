<?php

namespace Iberbanco\SDK\DTOs\User;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;

class ListUsersDTO extends BaseDTO
{
    public ?int $per_page = null;
    public ?int $page = null;
    public ?string $country = null;
    public ?string $status = null;
    public ?string $email = null;
    public ?int $type = null;
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?string $first_name = null;
    public ?string $last_name = null;

    public function validate(): void
    {
        if ($this->per_page !== null && ($this->per_page < 1 || $this->per_page > 100)) {
            throw ValidationException::invalidValue('per_page', $this->per_page, ['1-100']);
        }

        if ($this->page !== null && $this->page < 1) {
            throw ValidationException::minimumValue('page', $this->page, 1);
        }

        if ($this->country && strlen($this->country) !== 2) {
            throw ValidationException::invalidFormat('country', 'ISO 3166-1 alpha-2 (2 letters)');
        }

        if ($this->type !== null && !in_array($this->type, [1, 2])) {
            throw ValidationException::invalidValue('type', $this->type, [1, 2]);
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
    }

    public function getRequiredFields(): array
    {
        return []; // No required fields for listing
    }
} 