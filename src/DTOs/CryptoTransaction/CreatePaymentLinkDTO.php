<?php

namespace Iberbanco\SDK\DTOs\CryptoTransaction;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

class CreatePaymentLinkDTO extends BaseDTO
{
    public ?string $email = null;
    public ?string $order_id = null;
    public ?float $fiat_amount = null;
    public ?string $fiat_currency = null; // USD, EUR, GBP, CAD, TRY
    public ?string $redirect_url = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->email) {
            ValidationUtils::validateEmail($this->email);
        }

        if ($this->order_id) {
            ValidationUtils::validateLength($this->order_id, 1, 255, 'order_id');
        }

        if ($this->fiat_amount !== null && $this->fiat_amount < 1) {
            throw ValidationException::minimumValue('fiat_amount', $this->fiat_amount, 1);
        }

        if ($this->fiat_currency && !in_array($this->fiat_currency, ValidationUtils::CRYPTO_PAYMENT_CURRENCIES)) {
            throw ValidationException::invalidValue('fiat_currency', $this->fiat_currency, ValidationUtils::CRYPTO_PAYMENT_CURRENCIES);
        }

        if ($this->redirect_url) {
            ValidationUtils::validateUrl($this->redirect_url, 2048, 'redirect_url');
        }
    }

    public function getRequiredFields(): array
    {
        return ['email', 'order_id', 'fiat_amount', 'fiat_currency'];
    }
} 