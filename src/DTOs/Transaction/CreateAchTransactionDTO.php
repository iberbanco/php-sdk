<?php

namespace Iberbanco\SDK\DTOs\Transaction;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

class CreateAchTransactionDTO extends BaseDTO
{
    public ?string $account_number = null;
    public ?float $amount = null;
    public ?string $reference = null;
    public ?string $beneficiary_account_number = null;
    public ?string $beneficiary_name = null;
    public ?string $beneficiary_address = null;
    public ?string $beneficiary_email = null;
    public ?string $institution_number = null;
    public ?string $transit_number = null;
    public ?string $bank_name = null;
    public ?string $bank_country = null;
    public ?string $bank_address = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->amount !== null && $this->amount < 0.01) {
            throw ValidationException::minimumValue('amount', $this->amount, 0.01);
        }

        if ($this->beneficiary_email) {
            ValidationUtils::validateEmail($this->beneficiary_email);
        }

        if ($this->account_number) {
            ValidationUtils::validateLength($this->account_number, 1, 255, 'account_number');
        }

        if ($this->beneficiary_account_number) {
            ValidationUtils::validateLength($this->beneficiary_account_number, 1, 255, 'beneficiary_account_number');
        }

        if ($this->institution_number) {
            ValidationUtils::validateLength($this->institution_number, 1, 50, 'institution_number');
        }

        if ($this->transit_number) {
            ValidationUtils::validateLength($this->transit_number, 1, 50, 'transit_number');
        }
    }

    public function getRequiredFields(): array
    {
        return [
            'account_number',
            'amount',
            'reference',
            'beneficiary_account_number',
            'beneficiary_name',
            'beneficiary_address',
            'beneficiary_email',
            'institution_number',
            'transit_number',
            'bank_name',
            'bank_country',
            'bank_address'
        ];
    }
} 