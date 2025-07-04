<?php

namespace Iberbanco\SDK\DTOs\Transaction;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

class CreateSepaTransactionDTO extends BaseDTO
{
    public ?string $account_number = null;
    public ?float $amount = null;
    public ?string $reference = null;
    public ?string $iban_code = null;
    public ?string $beneficiary_name = null;
    public ?string $beneficiary_country = null;
    public ?string $beneficiary_state = null;
    public ?string $beneficiary_city = null;
    public ?string $beneficiary_address = null;
    public ?string $beneficiary_zip_code = null;
    public ?string $beneficiary_email = null;
    public ?string $swift_code = null;
    public ?string $bank_name = null;
    public ?string $bank_country = null;
    public ?string $bank_state = null;
    public ?string $bank_city = null;
    public ?string $bank_address = null;
    public ?string $bank_zip_code = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->amount !== null && $this->amount < 0.01) {
            throw ValidationException::minimumValue('amount', $this->amount, 0.01);
        }

        if ($this->iban_code) {
            ValidationUtils::validateIban($this->iban_code, 'iban_code');
        }

        if ($this->beneficiary_email) {
            ValidationUtils::validateEmail($this->beneficiary_email);
        }

        if ($this->account_number) {
            ValidationUtils::validateLength($this->account_number, 1, 255, 'account_number');
        }

        if ($this->beneficiary_name) {
            ValidationUtils::validateLength($this->beneficiary_name, 2, 255, 'beneficiary_name');
        }
    }

    public function getRequiredFields(): array
    {
        return [
            'account_number',
            'amount',
            'reference',
            'iban_code',
            'beneficiary_name',
            'beneficiary_country',
            'beneficiary_state',
            'beneficiary_city',
            'beneficiary_address',
            'beneficiary_zip_code',
            'beneficiary_email',
            'swift_code',
            'bank_name',
            'bank_country',
            'bank_state',
            'bank_city',
            'bank_address',
            'bank_zip_code'
        ];
    }


} 