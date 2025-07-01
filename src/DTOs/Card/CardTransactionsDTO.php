<?php

namespace Iberbanco\SDK\DTOs\Card;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;

class CardTransactionsDTO extends BaseDTO
{
    public ?string $remote_id = null;
    public ?string $userNumber = null;
    public ?string $san = null;
    public ?int $year = null;
    public ?int $month = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->remote_id && strlen($this->remote_id) > 50) {
            throw ValidationException::maximumValue('remote_id', strlen($this->remote_id), 50);
        }

        if ($this->userNumber && strlen($this->userNumber) > 50) {
            throw ValidationException::maximumValue('userNumber', strlen($this->userNumber), 50);
        }

        if ($this->san && strlen($this->san) > 50) {
            throw ValidationException::maximumValue('san', strlen($this->san), 50);
        }

        if ($this->year !== null) {
            $currentYear = (int)date('Y');
            if ($this->year < 2020) {
                throw ValidationException::minimumValue('year', $this->year, 2020);
            }
            if ($this->year > ($currentYear + 1)) {
                throw ValidationException::maximumValue('year', $this->year, $currentYear + 1);
            }
        }

        if ($this->month !== null) {
            if ($this->month < 1) {
                throw ValidationException::minimumValue('month', $this->month, 1);
            }
            if ($this->month > 12) {
                throw ValidationException::maximumValue('month', $this->month, 12);
            }
        }
    }

    public function getRequiredFields(): array
    {
        return ['remote_id', 'userNumber', 'san', 'year', 'month'];
    }
} 