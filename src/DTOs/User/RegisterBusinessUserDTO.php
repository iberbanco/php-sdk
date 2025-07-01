<?php

namespace Iberbanco\SDK\DTOs\User;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

class RegisterBusinessUserDTO extends BaseDTO
{
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $date_of_birth = null;
    public ?array $address = null; // Contains: street, city, state, postal_code, country
    public ?int $preferred_currency = null;
    public ?bool $terms_accepted = null;
    public ?bool $marketing_consent = null;
    public ?string $identity_document_type = null; // passport, national_id, driving_license
    public ?string $identity_document_number = null;
    public ?string $company_name = null;
    public ?string $company_registration_number = null;
    public ?array $selected_service = null; // Array of service IDs

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

        if ($this->phone) {
            ValidationUtils::validatePhoneNumber($this->phone);
        }

        if ($this->date_of_birth) {
            ValidationUtils::validateDateOfBirth($this->date_of_birth);
        }

        if ($this->address) {
            ValidationUtils::validateAddress($this->address);
        }

        if ($this->identity_document_type && !in_array($this->identity_document_type, ValidationUtils::IDENTITY_DOCUMENT_TYPES)) {
            throw ValidationException::invalidValue('identity_document_type', $this->identity_document_type, ValidationUtils::IDENTITY_DOCUMENT_TYPES);
        }

        if ($this->identity_document_number) {
            ValidationUtils::validateIdentityDocumentNumber($this->identity_document_number);
        }

        if ($this->company_name) {
            ValidationUtils::validateLength($this->company_name, 2, 255, 'company_name');
        }

        if ($this->company_registration_number) {
            ValidationUtils::validateLength($this->company_registration_number, 3, 255, 'company_registration_number');
        }

        if ($this->selected_service && is_array($this->selected_service)) {
            $validServices = [1, 2, 3]; // Assuming: 1=card, 2=crypto, 3=bank
            foreach ($this->selected_service as $service) {
                if (!in_array($service, $validServices)) {
                    throw ValidationException::invalidValue('selected_service', $service, $validServices);
                }
            }
        }

        if ($this->terms_accepted !== true) {
            throw ValidationException::requiredField('terms_accepted');
        }
    }

    public function getRequiredFields(): array
    {
        return [
            'first_name', 'last_name', 'email', 'date_of_birth', 'address',
            'preferred_currency', 'terms_accepted', 'identity_document_type',
            'identity_document_number', 'company_name', 'company_registration_number'
        ];
    }


} 