<?php

namespace Iberbanco\SDK\DTOs\Account;

use Iberbanco\SDK\DTOs\BaseDTO;

class TotalBalanceDTO extends BaseDTO
{
    public mixed $currency = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->currency) {
            $this->validateCurrency($this->currency);
        }
    }

    public function getRequiredFields(): array
    {
        return ['currency'];
    }
} 