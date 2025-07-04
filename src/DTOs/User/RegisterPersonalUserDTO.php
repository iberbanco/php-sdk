<?php

namespace Iberbanco\SDK\DTOs\User;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

class RegisterPersonalUserDTO extends BaseDTO
{
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $call_number = null;
    public ?string $date_of_birth = null;
    public ?string $citizenship = null;
    
    // Flat address fields (matching API structure)
    public ?string $address = null;
    public ?string $city = null;
    public ?string $state_or_province = null;
    public ?string $post_code = null;
    public ?string $country = null;
    
    public ?array $currencies = null;
    public ?array $selected_service = null;
    public ?array $sources_of_wealth = null;
    public ?bool $is_pep = null;
    public ?bool $terms_accepted = null;
    public ?string $identity_document_type = null;
    public ?string $identity_document_number = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->first_name) {
            ValidationUtils::validateLength($this->first_name, 2, 50, 'first_name');
            ValidationUtils::validateNameFormat($this->first_name, 'first_name');
        }

        if ($this->last_name) {
            ValidationUtils::validateLength($this->last_name, 2, 50, 'last_name');
            ValidationUtils::validateNameFormat($this->last_name, 'last_name');
        }

        if ($this->email) {
            ValidationUtils::validateEmail($this->email);
            ValidationUtils::validateLength($this->email, 1, 255, 'email');
        }

        if ($this->call_number) {
            ValidationUtils::validatePhoneNumber($this->call_number);
        }

        if ($this->date_of_birth) {
            ValidationUtils::validateDateOfBirth($this->date_of_birth);
        }

        if ($this->citizenship) {
            ValidationUtils::validateLength($this->citizenship, 2, 2, 'citizenship');
        }

        if ($this->identity_document_type && !in_array($this->identity_document_type, ValidationUtils::IDENTITY_DOCUMENT_TYPES)) {
            throw ValidationException::invalidValue('identity_document_type', $this->identity_document_type, ValidationUtils::IDENTITY_DOCUMENT_TYPES);
        }

        if ($this->identity_document_number) {
            ValidationUtils::validateIdentityDocumentNumber($this->identity_document_number);
        }

        if ($this->terms_accepted !== true) {
            throw ValidationException::requiredField('terms_accepted');
        }
    }

    public function getRequiredFields(): array
    {
        // Basic required fields for card+crypto services
        return [
            'first_name', 'last_name', 'email', 'password', 'call_number',
            'date_of_birth', 'citizenship', 'address', 'city', 'state_or_province',
            'post_code', 'country', 'currencies', 'selected_service', 'terms_accepted'
        ];
    }
} 