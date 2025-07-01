<?php

namespace Iberbanco\SDK\DTOs\Account;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;

class CreateAccountDTO extends BaseDTO
{
    public ?string $user_number = null;
    public mixed $currency = null; // Can be int, string, or array
    public ?string $reference = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->user_number && strlen($this->user_number) < 6) {
            throw ValidationException::minimumValue('user_number', $this->user_number, 6);
        }

        if ($this->currency) {
            if (is_array($this->currency)) {
                foreach ($this->currency as $curr) {
                    $this->validateCurrency($curr);
                }
            } else {
                $this->validateCurrency($this->currency);
            }
        }

        if ($this->reference && strlen($this->reference) > 255) {
            throw ValidationException::maximumValue('reference', strlen($this->reference), 255);
        }
    }

    public function getRequiredFields(): array
    {
        return ['user_number', 'currency'];
    }
} 