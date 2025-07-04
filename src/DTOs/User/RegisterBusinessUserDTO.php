<?php

namespace Iberbanco\SDK\DTOs\User;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Utils\ValidationUtils;

class RegisterBusinessUserDTO extends BaseDTO
{
    public ?string $email = null;
    public ?string $password = null;
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $salutation = null;
    public ?string $call_number = null;
    public ?string $date_of_birth = null;
    public ?string $address = null;
    public ?string $city = null;
    public ?string $country = null;
    public ?string $state_or_province = null;
    public ?string $fax = null;
    public ?int $type = null;
    public ?string $post_code = null;
    public ?int $identity_card_type = null;
    public ?string $identity_card_id = null;
    public ?string $tax_number = null;
    public ?string $citizenship = null;
    public ?array $currencies = null;
    public ?array $sources_of_wealth = null;
    public ?bool $is_other_sources_of_wealth = null;
    public ?string $company_name = null;
    public ?int $company_type = null;
    public ?string $registration_date = null;
    public ?string $registration_number = null;
    public ?int $nature_of_business = null;
    public ?string $financial_regulator = null;
    public ?string $regulatory_license_number = null;
    public ?string $website = null;
    public ?string $marketing_strategy = null;
    public ?string $industry_id = null;
    public ?string $authorized_person_country_of_residence = null;
    public ?string $authorized_person_city = null;
    public ?string $authorized_person_address = null;
    public ?string $authorized_person_postal_code = null;
    public ?array $selected_service = null;

    public function validate(): void
    {
        $this->validateRequired($this->getRequiredFields());

        if ($this->first_name) {
            ValidationUtils::validateLength($this->first_name, 2, 60, 'first_name');
            ValidationUtils::validateNameFormat($this->first_name, 'first_name');
        }

        if ($this->last_name) {
            ValidationUtils::validateLength($this->last_name, 2, 60, 'last_name');
            ValidationUtils::validateNameFormat($this->last_name, 'last_name');
        }

        if ($this->email) {
            ValidationUtils::validateEmail($this->email);
            ValidationUtils::validateLength($this->email, 1, 60, 'email');
        }

        if ($this->call_number) {
            ValidationUtils::validatePhoneNumber($this->call_number);
        }

        if ($this->date_of_birth) {
            ValidationUtils::validateDateOfBirth($this->date_of_birth);
        }

        if ($this->address) {
            ValidationUtils::validateLength($this->address, 1, 255, 'address');
        }

        if ($this->city) {
            ValidationUtils::validateLength($this->city, 1, 60, 'city');
        }

        if ($this->post_code) {
            ValidationUtils::validateLength($this->post_code, 1, 60, 'post_code');
        }

        if ($this->company_name) {
            ValidationUtils::validateLength($this->company_name, 2, 90, 'company_name');
        }

        if ($this->registration_number) {
            ValidationUtils::validateLength($this->registration_number, 1, 50, 'registration_number');
        }

        if ($this->identity_card_id) {
            ValidationUtils::validateLength($this->identity_card_id, 1, 255, 'identity_card_id');
        }

        if ($this->tax_number) {
            ValidationUtils::validateLength($this->tax_number, 1, 255, 'tax_number');
        }

        if ($this->selected_service && is_array($this->selected_service)) {
            $validServices = ['card', 'crypto', 'bank'];
            foreach ($this->selected_service as $service) {
                if (!in_array($service, $validServices)) {
                    throw ValidationException::invalidValue('selected_service', $service, $validServices);
                }
            }
        }
    }

    public function getRequiredFields(): array
    {
        return [
            'email', 'password', 'first_name', 'last_name', 'call_number',
            'date_of_birth', 'address', 'city', 'country', 'post_code',
            'identity_card_type', 'identity_card_id', 'tax_number', 'citizenship',
            'currencies', 'sources_of_wealth', 'company_name', 'company_type',
            'registration_date', 'registration_number', 'nature_of_business',
            'financial_regulator', 'regulatory_license_number', 'industry_id',
            'authorized_person_country_of_residence', 'authorized_person_city',
            'authorized_person_address', 'authorized_person_postal_code'
        ];
    }


} 