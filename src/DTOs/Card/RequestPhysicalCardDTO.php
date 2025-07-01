<?php

namespace Iberbanco\SDK\DTOs\Card;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;

class RequestPhysicalCardDTO extends BaseDTO
{
    public ?string $remote_id = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->remote_id && strlen($this->remote_id) > 50) {
            throw ValidationException::maximumValue('remote_id', strlen($this->remote_id), 50);
        }
    }

    public function getRequiredFields(): array
    {
        return ['remote_id'];
    }
} 