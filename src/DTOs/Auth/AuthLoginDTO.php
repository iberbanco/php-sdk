<?php

namespace Iberbanco\SDK\DTOs\Auth;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;

class AuthLoginDTO extends BaseDTO
{
    public ?string $username = null;
    public ?string $password = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->username && strlen($this->username) < 3) {
            throw ValidationException::minimumValue('username', strlen($this->username), 3);
        }

        if ($this->username && strlen($this->username) > 255) {
            throw ValidationException::maximumValue('username', strlen($this->username), 255);
        }

        if ($this->password && strlen($this->password) < 6) {
            throw ValidationException::minimumValue('password', strlen($this->password), 6);
        }
    }

    public function getRequiredFields(): array
    {
        return ['username', 'password'];
    }
} 