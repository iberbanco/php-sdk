<?php

namespace Iberbanco\SDK\Services;

use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

class ExchangeService extends AbstractService
{
    public function getRate(array $rateParams): array
    {
        $this->validateRateParams($rateParams);
        return $this->get('exchange/rate', $rateParams);
    }

    public function getConversion(string $fromCurrency, string $toCurrency, float $amount, array $options = []): array
    {
        $params = array_merge([
            'from' => $fromCurrency,
            'to' => $toCurrency,
            'amount' => $amount
        ], $options);

        return $this->getRate($params);
    }

    public function getMultipleRates(string $baseCurrency, array $targetCurrencies, array $options = []): array
    {
        $this->validateCurrency($baseCurrency);
        
        foreach ($targetCurrencies as $currency) {
            $this->validateCurrency($currency);
        }

        $params = array_merge([
            'base' => $baseCurrency,
            'targets' => implode(',', $targetCurrencies)
        ], $options);

        return $this->getRate($params);
    }

    public function getHistoricalRate(string $fromCurrency, string $toCurrency, string $date, array $options = []): array
    {
        $params = array_merge([
            'from' => $fromCurrency,
            'to' => $toCurrency,
            'date' => $date
        ], $options);

        return $this->getRate($params);
    }

    private function validateRateParams(array $rateParams): void
    {
        if (isset($rateParams['from']) && isset($rateParams['to'])) {
            $this->validateCurrency($rateParams['from']);
            $this->validateCurrency($rateParams['to']);

            if (strtoupper($rateParams['from']) === strtoupper($rateParams['to'])) {
                throw ValidationException::invalidValue(
                    'currency_pair',
                    $rateParams['from'] . '-' . $rateParams['to'],
                    ['Source and target currencies must be different']
                );
            }
        }

        if (isset($rateParams['base'])) {
            $this->validateCurrency($rateParams['base']);
        }

        if (isset($rateParams['targets'])) {
            $targets = is_string($rateParams['targets']) 
                ? explode(',', $rateParams['targets']) 
                : $rateParams['targets'];
                
            foreach ($targets as $currency) {
                $this->validateCurrency(trim($currency));
            }
        }

        if (isset($rateParams['amount'])) {
            $amount = (float)$rateParams['amount'];
            if ($amount <= 0) {
                throw ValidationException::minimumValue('amount', $amount, 0.01);
            }

            $maxAmount = 10000000; // 10 million
            if ($amount > $maxAmount) {
                throw ValidationException::maximumValue('amount', $amount, $maxAmount);
            }
        }

        if (isset($rateParams['date'])) {
            try {
                $date = new \DateTime($rateParams['date']);
                $now = new \DateTime();
                
                if ($date > $now) {
                    throw ValidationException::invalidValue('date', $rateParams['date'], ['Date cannot be in the future']);
                }

                $oneYearAgo = $now->sub(new \DateInterval('P1Y'));
                if ($date < $oneYearAgo) {
                    throw ValidationException::invalidValue('date', $rateParams['date'], ['Date cannot be more than 1 year ago']);
                }
                
            } catch (\Exception $e) {
                throw ValidationException::invalidFormat('date', 'Y-m-d');
            }
        }

        if (isset($rateParams['precision'])) {
            $precision = (int)$rateParams['precision'];
            if ($precision < 2 || $precision > 8) {
                throw ValidationException::invalidValue('precision', $precision, ['2-8']);
            }
        }
    }

    private function validateCurrency(string $currency): void
    {
        $supportedCurrencies = $this->getSupportedCurrencies();
        
        $currency = strtoupper(trim($currency));
        if (!in_array($currency, array_keys($supportedCurrencies))) {
            throw ValidationException::invalidCurrency($currency);
        }
    }

    public function getSupportedCurrencies(): array
    {
        return [
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound Sterling',
            'CHF' => 'Swiss Franc',
            'CAD' => 'Canadian Dollar',
            'AUD' => 'Australian Dollar',
            'JPY' => 'Japanese Yen',
            'SEK' => 'Swedish Krona',
            'NOK' => 'Norwegian Krone',
            'DKK' => 'Danish Krone',
            'PLN' => 'Polish Zloty',
            'CZK' => 'Czech Koruna',
            'HUF' => 'Hungarian Forint',
            'RON' => 'Romanian Leu',
            'BGN' => 'Bulgarian Lev',
            'HRK' => 'Croatian Kuna',
            'RUB' => 'Russian Ruble',
            'CNY' => 'Chinese Yuan',
            'INR' => 'Indian Rupee',
            'BRL' => 'Brazilian Real',
            'MXN' => 'Mexican Peso',
            'SGD' => 'Singapore Dollar',
            'HKD' => 'Hong Kong Dollar',
            'NZD' => 'New Zealand Dollar',
            'ZAR' => 'South African Rand'
        ];
    }

    public function getMajorPairs(): array
    {
        return [
            'EUR/USD' => 'Euro to US Dollar',
            'GBP/USD' => 'British Pound to US Dollar',
            'USD/JPY' => 'US Dollar to Japanese Yen',
            'USD/CHF' => 'US Dollar to Swiss Franc',
            'AUD/USD' => 'Australian Dollar to US Dollar',
            'USD/CAD' => 'US Dollar to Canadian Dollar',
            'NZD/USD' => 'New Zealand Dollar to US Dollar'
        ];
    }

    public function getRateTypes(): array
    {
        return [
            'spot' => 'Spot Rate (Current)',
            'buy' => 'Buy Rate',
            'sell' => 'Sell Rate',
            'mid' => 'Mid Rate',
            'historical' => 'Historical Rate'
        ];
    }

    public function getSupportedPrecisionLevels(): array
    {
        return [
            2 => '2 decimal places',
            4 => '4 decimal places',
            6 => '6 decimal places',
            8 => '8 decimal places'
        ];
    }
} 