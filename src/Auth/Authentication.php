<?php

namespace Iberbanco\SDK\Auth;

use Iberbanco\SDK\Exceptions\AuthenticationException;

class Authentication
{
    private string $username;
    private ?string $token = null;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function generateAuthHeaders(?string $token = null, ?int $timestamp = null): array
    {
        $token = $token ?: $this->token;
        $timestamp = $timestamp ?: time();

        if (!$token) {
            throw new AuthenticationException('Authentication token is required');
        }

        if (empty($this->username)) {
            throw new AuthenticationException('Username is required for hash generation');
        }

        $hash = $this->generateHash($token, $timestamp, $this->username);

        return [
            'token' => $token,
            'timestamp' => (string)$timestamp,
            'hash' => $hash
        ];
    }

    public function generateHash(string $token, int $timestamp, string $username): string
    {
        $data = $token . $timestamp;
        return hash_hmac('sha256', $data, $username);
    }

    public function verifyHash(string $token, int $timestamp, string $username, string $providedHash): bool
    {
        $expectedHash = $this->generateHash($token, $timestamp, $username);
        return hash_equals($expectedHash, $providedHash);
    }

    public function generateAgentAdminHeaders(string $token, string $pin): array
    {
        return [
            'token' => $token,
            'pin' => $pin
        ];
    }

    public function generateDashboardHeaders(string $token): array
    {
        return [
            'token' => $token
        ];
    }

    public function validateTimestamp(int $timestamp, int $tolerance = 300): bool
    {
        $currentTime = time();
        $diff = abs($currentTime - $timestamp);
        return $diff <= $tolerance;
    }

    public function isTokenExpired(string $tokenExpireAt): bool
    {
        $expireTime = strtotime($tokenExpireAt);
        return time() > $expireTime;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }
} 