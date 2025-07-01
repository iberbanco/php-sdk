<?php

namespace Iberbanco\SDK\Services;

use Iberbanco\SDK\Exceptions\AuthenticationException;
use Iberbanco\SDK\Exceptions\ValidationException;

class AuthService extends AbstractService
{
    public function authenticate(string $username, string $password): array
    {
        $this->validateCredentials($username, $password);

        try {
            $response = $this->httpClient->post('auth', [
                'username' => $username,
                'password' => $password
            ]);

            if (!$this->isSuccessfulResponse($response)) {
                throw AuthenticationException::invalidCredentials([
                    $response['message'] ?? 'Authentication failed'
                ]);
            }

            return $response;

        } catch (\Exception $e) {
            if ($e instanceof AuthenticationException) {
                throw $e;
            }
            
            throw AuthenticationException::invalidCredentials([
                'Authentication request failed: ' . $e->getMessage()
            ]);
        }
    }

    private function validateCredentials(string $username, string $password): void
    {
        $errors = [];

        if (empty(trim($username))) {
            $errors[] = 'Username is required';
        }

        if (empty(trim($password))) {
            $errors[] = 'Password is required';
        }

        if (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }

        if (!empty($errors)) {
            throw ValidationException::fieldValidation($errors, 'Invalid authentication credentials');
        }
    }

    public function isTokenValid(): bool
    {
        $token = $this->auth->getToken();
        return $token !== null && !empty(trim($token));
    }

    public function getToken(): ?string
    {
        return $this->auth->getToken();
    }

    public function setToken(string $token): self
    {
        $this->auth->setToken($token);
        return $this;
    }

    public function clearToken(): self
    {
        $this->auth->setToken('');
        return $this;
    }

    public function generateAuthHeaders(?string $token = null, ?int $timestamp = null): array
    {
        return $this->auth->generateAuthHeaders($token, $timestamp);
    }

    public function verifyHash(string $token, int $timestamp, string $username, string $hash): bool
    {
        return $this->auth->verifyHash($token, $timestamp, $username, $hash);
    }

    public function validateTimestamp(int $timestamp, int $tolerance = 300): bool
    {
        return $this->auth->validateTimestamp($timestamp, $tolerance);
    }
} 