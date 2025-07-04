<?php

namespace Iberbanco\SDK\DTOs\Exchange;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;

class GetRateDTO extends BaseDTO
{
    public ?string $from = null;
    public ?string $to = null;
    public ?float $amount = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->from) {
            $this->validateExchangeCurrency($this->from, 'from');
        }

        if ($this->to) {
            $this->validateExchangeCurrency($this->to, 'to');
        }

        if ($this->from && $this->to && strtoupper($this->from) === strtoupper($this->to)) {
            throw ValidationException::invalidValue(
                'currency_pair',
                $this->from . '-' . $this->to,
                ['Source and target currencies must be different']
            );
        }

        if ($this->amount !== null) {
            if ($this->amount < 0.01) {
                throw ValidationException::minimumValue('amount', $this->amount, 0.01);
            }

            if ($this->amount > 999999999.99) {
                throw ValidationException::maximumValue('amount', $this->amount, 999999999.99);
            }
        }
    }

    public function getRequiredFields(): array
    {
        return ['from', 'to', 'amount'];
    }

    private function validateExchangeCurrency(string $currency, string $fieldName): void
    {
        // Valid currency codes from the API
        $supportedCurrencies = [
            'USD', 'EUR', 'GBP', 'CHF', 'RUB', 'TRY', 'AED', 'CNH',
            'AUD', 'CZK', 'PLN', 'CAD', 'USDT', 'HKD', 'SGD', 'JPY'
        ];
        
        if (!in_array(strtoupper($currency), $supportedCurrencies)) {
            throw ValidationException::invalidCurrency($currency);
        }
    }
} 