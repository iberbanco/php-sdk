<?php

namespace Iberbanco\SDK\Services;

use Iberbanco\SDK\DTOs\User\ListUsersDTO;
use Iberbanco\SDK\DTOs\User\RegisterPersonalUserDTO;
use Iberbanco\SDK\DTOs\User\RegisterBusinessUserDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

class UserService extends AbstractService
{
    public function list($filters = []): array
    {
        if (is_array($filters)) {
            $filters = ListUsersDTO::fromArray($filters);
        } elseif (!$filters instanceof ListUsersDTO) {
            throw new \InvalidArgumentException('Filters must be an array or ListUsersDTO instance');
        }

        $queryParams = $filters->toArray();
        return $this->get('users', $queryParams);
    }

    public function registerPersonal($userData): array
    {
        if (is_array($userData)) {
            $userData = RegisterPersonalUserDTO::fromArray($userData);
        } elseif (!$userData instanceof RegisterPersonalUserDTO) {
            throw new \InvalidArgumentException('User data must be an array or RegisterPersonalUserDTO instance');
        }

        $dataArray = $userData->toArray();
        $dataArray['type'] = 1; // Personal user type
        
        return $this->post('users/register/personal', $dataArray);
    }

    public function registerBusiness($userData): array
    {
        if (is_array($userData)) {
            $userData = RegisterBusinessUserDTO::fromArray($userData);
        } elseif (!$userData instanceof RegisterBusinessUserDTO) {
            throw new \InvalidArgumentException('User data must be an array or RegisterBusinessUserDTO instance');
        }

        $dataArray = $userData->toArray();
        $dataArray['type'] = 2; // Business user type
        
        return $this->post('users/register/business', $dataArray);
    }

    private function buildUserListQuery(array $filters): array
    {
        $allowedFilters = [
            'per_page', 'page', 'country', 'status', 'email', 'type',
            'date_from', 'date_to', 'first_name', 'last_name'
        ];

        $query = [];
        foreach ($allowedFilters as $filter) {
            if (isset($filters[$filter]) && $filters[$filter] !== null && $filters[$filter] !== '') {
                $query[$filter] = $filters[$filter];
            }
        }

        if (!isset($query['per_page'])) {
            $query['per_page'] = 50;
        }

        if (isset($query['per_page'])) {
            $query['per_page'] = max(1, min((int)$query['per_page'], 100));
        }

        return $query;
    }

    private function validatePersonalUserData(array $userData): void
    {
        $requiredFields = [
            'email', 'password', 'first_name', 'last_name', 
            'date_of_birth', 'country', 'address', 'city', 
            'post_code', 'call_number', 'citizenship'
        ];

        $this->validateRequired($userData, $requiredFields);
        $this->validateCommonUserData($userData);

        if (isset($userData['date_of_birth'])) {
            $this->validateDateOfBirth($userData['date_of_birth']);
        }
    }

    private function validateBusinessUserData(array $userData): void
    {
        $requiredFields = [
            'email', 'password', 'first_name', 'last_name',
            'country', 'address', 'city', 'post_code',
            'call_number', 'company_name', 'company_registration_number'
        ];

        $this->validateRequired($userData, $requiredFields);
        $this->validateCommonUserData($userData);

        if (isset($userData['company_name']) && strlen($userData['company_name']) < 2) {
            throw ValidationException::minimumValue('company_name', $userData['company_name'], 2);
        }

        if (isset($userData['company_registration_number']) && strlen($userData['company_registration_number']) < 3) {
            throw ValidationException::minimumValue('company_registration_number', $userData['company_registration_number'], 3);
        }
    }

    private function validateCommonUserData(array $userData): void
    {
        if (isset($userData['email']) && !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::invalidEmail($userData['email']);
        }

        if (isset($userData['password']) && strlen($userData['password']) < 8) {
            throw ValidationException::minimumValue('password', strlen($userData['password']), 8);
        }

        if (isset($userData['first_name']) && strlen($userData['first_name']) < 2) {
            throw ValidationException::minimumValue('first_name', $userData['first_name'], 2);
        }

        if (isset($userData['last_name']) && strlen($userData['last_name']) < 2) {
            throw ValidationException::minimumValue('last_name', $userData['last_name'], 2);
        }

        if (isset($userData['country']) && strlen($userData['country']) !== 2) {
            throw ValidationException::invalidFormat('country', 'ISO 3166-1 alpha-2 (2 letters)');
        }

        if (isset($userData['call_number']) && !preg_match('/^\+?[1-9]\d{1,14}$/', $userData['call_number'])) {
            throw ValidationException::invalidFormat('call_number', 'valid phone number');
        }

        if (isset($userData['post_code']) && strlen($userData['post_code']) < 3) {
            throw ValidationException::minimumValue('post_code', $userData['post_code'], 3);
        }
    }

    private function validateDateOfBirth(string $dateOfBirth): void
    {
        $date = \DateTime::createFromFormat('Y-m-d', $dateOfBirth);
        
        if (!$date || $date->format('Y-m-d') !== $dateOfBirth) {
            throw ValidationException::invalidFormat('date_of_birth', 'Y-m-d');
        }

        $now = new \DateTime();
        $age = $now->diff($date)->y;
        
        if ($age < 18) {
            throw ValidationException::minimumValue('age', $age, 18);
        }

        if ($date > $now) {
            throw ValidationException::invalidValue('date_of_birth', $dateOfBirth, ['Date cannot be in the future']);
        }
    }

    public function getSupportedCountries(): array
    {
        return [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'FR' => 'France',
            'ES' => 'Spain',
            'IT' => 'Italy',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'CH' => 'Switzerland',
            'AT' => 'Austria',
            'PT' => 'Portugal',
            'IE' => 'Ireland',
            'LU' => 'Luxembourg',
            'NO' => 'Norway',
            'SE' => 'Sweden',
            'DK' => 'Denmark',
            'FI' => 'Finland',
        ];
    }

    public function getSupportedUserTypes(): array
    {
        return [
            1 => 'Personal',
            2 => 'Business'
        ];
    }
} 