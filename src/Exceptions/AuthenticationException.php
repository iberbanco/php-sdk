<?php

namespace Iberbanco\SDK\Exceptions;

class AuthenticationException extends IberbancoException
{
    public static function invalidCredentials(array $errors = []): self
    {
        return new self(
            'Invalid authentication credentials',
            401,
            $errors
        );
    }

    public static function tokenExpired(array $errors = []): self
    {
        return new self(
            'Authentication token has expired',
            401,
            $errors
        );
    }

    public static function missingToken(array $errors = []): self
    {
        return new self(
            'Authentication token is missing',
            401,
            $errors
        );
    }

    public static function invalidHash(array $errors = []): self
    {
        return new self(
            'Invalid authentication hash',
            401,
            $errors
        );
    }

    public static function invalidTimestamp(array $errors = []): self
    {
        return new self(
            'Invalid or expired timestamp',
            401,
            $errors
        );
    }

    public static function agentNotFound(array $errors = []): self
    {
        return new self(
            'Agent not found',
            404,
            $errors
        );
    }

    public static function agentInactive(array $errors = []): self
    {
        return new self(
            'Agent account is not active',
            401,
            $errors
        );
    }

    public static function missingPin(array $errors = []): self
    {
        return new self(
            'PIN is required for agent admin authentication',
            401,
            $errors
        );
    }

    public static function invalidPin(array $errors = []): self
    {
        return new self(
            'Invalid PIN provided',
            401,
            $errors
        );
    }
} 