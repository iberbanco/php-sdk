<?php

namespace Iberbanco\SDK\DTOs\Transaction;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

class CreateAchTransactionDTO extends BaseDTO
{
    public ?string $account_number = null;
    public ?float $amount = null;
    public ?string $recipient_account_number = null;
    public ?string $recipient_routing_number = null;
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

        if ($this->recipient_routing_number) {
            ValidationUtils::validateRoutingNumber($this->recipient_routing_number, 'recipient_routing_number');
        }

        if ($this->account_number) {
            ValidationUtils::validateLength($this->account_number, 10, 255, 'account_number');
        }

        if ($this->recipient_account_number) {
            ValidationUtils::validateLength($this->recipient_account_number, 5, 255, 'recipient_account_number');
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
            'recipient_account_number',
            'recipient_routing_number'
        ];
    }
} 