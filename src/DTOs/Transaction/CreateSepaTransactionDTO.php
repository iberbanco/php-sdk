<?php

namespace Iberbanco\SDK\DTOs\Transaction;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

class CreateSepaTransactionDTO extends BaseDTO
{
    public ?string $account_number = null;
    public ?float $amount = null;
    public ?string $recipient_iban = null;
    public ?string $recipient_name = null;
    public ?string $reference = null;
    public ?string $description = null;
    public ?string $currency = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->amount !== null) {
            $this->validateAmount($this->amount);
        }

        if ($this->recipient_iban) {
            ValidationUtils::validateIban($this->recipient_iban, 'recipient_iban');
        }

        if ($this->account_number) {
            ValidationUtils::validateLength($this->account_number, 10, 255, 'account_number');
        }

        if ($this->recipient_name) {
            ValidationUtils::validateLength($this->recipient_name, 2, 255, 'recipient_name');
        }

        if ($this->currency) {
            $this->validateCurrency($this->currency);
        }
    }

    public function getRequiredFields(): array
    {
        return [
            'account_number',
            'amount',
            'recipient_iban',
            'recipient_name'
        ];
    }


} 