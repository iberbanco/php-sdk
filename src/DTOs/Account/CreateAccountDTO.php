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

        if ($this->user_number && strlen($this->user_number) > 50) {
            throw ValidationException::maximumValue('user_number', strlen($this->user_number), 50);
        }
        
        if ($this->user_number && !preg_match('/^[a-zA-Z0-9]+$/', $this->user_number)) {
            throw ValidationException::invalidFormat('user_number', 'alphanumeric characters only');
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