<?php

namespace Iberbanco\SDK\DTOs\Card;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

class CreateCardDTO extends BaseDTO
{
    public ?string $user_number = null;
    public ?string $account_number = null;
    public ?float $amount = null; // Initial card amount
    public ?int $currency = null; // 1=USD, 2=EUR
    public ?string $shipping_address = null;
    public ?string $shipping_city = null;
    public ?string $shipping_state = null;
    public ?string $shipping_country_code = null; // 2-letter country code
    public ?string $shipping_post_code = null;
    public ?string $delivery_method = null; // 'Standard' or 'Registered'
    public ?string $product_type = null; // Optional

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->user_number) {
            ValidationUtils::validateLength($this->user_number, 1, 60, 'user_number');
            ValidationUtils::validateAlphanumeric($this->user_number, 'user_number');
        }

        if ($this->account_number) {
            ValidationUtils::validateLength($this->account_number, 1, 255, 'account_number');
        }

        if ($this->amount !== null) {
            if ($this->amount < 1 || $this->amount > 5000) {
                throw ValidationException::range('amount', $this->amount, 1, 5000);
            }
        }

        if ($this->currency !== null && !in_array($this->currency, [1, 2])) {
            throw ValidationException::invalidValue('currency', $this->currency, [1, 2]);
        }

        if ($this->shipping_address) {
            ValidationUtils::validateLength($this->shipping_address, 1, 255, 'shipping_address');
        }

        if ($this->shipping_city) {
            ValidationUtils::validateLength($this->shipping_city, 1, 100, 'shipping_city');
        }

        if ($this->shipping_state) {
            ValidationUtils::validateLength($this->shipping_state, 1, 100, 'shipping_state');
        }

        if ($this->shipping_country_code) {
            ValidationUtils::validateCountryCode($this->shipping_country_code, 'shipping_country_code');
        }

        if ($this->shipping_post_code) {
            ValidationUtils::validateLength($this->shipping_post_code, 1, 20, 'shipping_post_code');
        }

        if ($this->delivery_method && !in_array($this->delivery_method, ['Standard', 'Registered'])) {
            throw ValidationException::invalidValue('delivery_method', $this->delivery_method, ['Standard', 'Registered']);
        }
    }

    public function getRequiredFields(): array
    {
        return [
            'user_number', 'account_number', 'amount', 'currency',
            'shipping_address', 'shipping_city', 'shipping_state',
            'shipping_country_code', 'shipping_post_code', 'delivery_method'
        ];
    }
} 