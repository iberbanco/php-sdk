<?php

namespace Iberbanco\SDK\DTOs\Exchange;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;

class GetRateDTO extends BaseDTO
{
    public ?string $from = null;
    public ?string $to = null;
    public ?float $amount = null;
    public ?string $date = null;
    public ?int $precision = null;

    public function validate(): void
    {
        if ($this->from && $this->to) {
            $this->validateExchangeCurrency($this->from, 'from');
            $this->validateExchangeCurrency($this->to, 'to');

            if (strtoupper($this->from) === strtoupper($this->to)) {
                throw ValidationException::invalidValue(
                    'currency_pair',
                    $this->from . '-' . $this->to,
                    ['Source and target currencies must be different']
                );
            }
        }

        if ($this->amount !== null) {
            if ($this->amount <= 0) {
                throw ValidationException::minimumValue('amount', $this->amount, 0.01);
            }

            $maxAmount = 10000000; // 10 million
            if ($this->amount > $maxAmount) {
                throw ValidationException::maximumValue('amount', $this->amount, $maxAmount);
            }
        }

        if ($this->date) {
            try {
                $date = new \DateTime($this->date);
                $now = new \DateTime();
                
                if ($date > $now) {
                    throw ValidationException::invalidValue('date', $this->date, ['Date cannot be in the future']);
                }

                $oneYearAgo = (clone $now)->sub(new \DateInterval('P1Y'));
                if ($date < $oneYearAgo) {
                    throw ValidationException::invalidValue('date', $this->date, ['Date cannot be more than 1 year ago']);
                }
                
            } catch (\Exception $e) {
                throw ValidationException::invalidFormat('date', 'Y-m-d');
            }
        }

        if ($this->precision !== null && ($this->precision < 2 || $this->precision > 8)) {
            throw ValidationException::invalidValue('precision', $this->precision, ['2-8']);
        }
    }

    public function getRequiredFields(): array
    {
        return []; // Flexible requirements based on use case
    }

    private function validateExchangeCurrency(string $currency, string $fieldName): void
    {
        $supportedCurrencies = [
            'USD', 'EUR', 'GBP', 'CHF', 'CAD', 'AUD', 'JPY', 'SEK',
            'NOK', 'DKK', 'PLN', 'CZK', 'HUF', 'RON', 'BGN', 'HRK',
            'RUB', 'CNY', 'INR', 'BRL', 'MXN', 'SGD', 'HKD', 'NZD', 'ZAR'
        ];
        
        if (!in_array(strtoupper($currency), $supportedCurrencies)) {
            throw ValidationException::invalidCurrency($currency);
        }
    }
} 