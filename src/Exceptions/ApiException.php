<?php

namespace Iberbanco\SDK\Exceptions;

class ApiException extends IberbancoException
{
    private int $httpStatusCode;
    private ?string $responseBody = null;

    public function __construct(
        string $message = '',
        int $httpStatusCode = 0,
        array $errors = [],
        ?string $responseBody = null,
        ?array $responseData = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $httpStatusCode, $errors, $responseData, $previous);
        $this->httpStatusCode = $httpStatusCode;
        $this->responseBody = $responseBody;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    public static function networkError(string $message, ?\Throwable $previous = null): self
    {
        return new self(
            'Network error: ' . $message,
            0,
            ['Network connectivity issue'],
            null,
            null,
            $previous
        );
    }

    public static function timeout(int $timeout): self
    {
        return new self(
            "Request timed out after {$timeout} seconds",
            0,
            ['Request timeout']
        );
    }

    public static function badRequest(string $message = 'Bad Request', array $errors = [], ?string $responseBody = null): self
    {
        return new self(
            $message,
            400,
            $errors,
            $responseBody
        );
    }

    public static function unauthorized(string $message = 'Unauthorized', array $errors = [], ?string $responseBody = null): self
    {
        return new self(
            $message,
            401,
            $errors,
            $responseBody
        );
    }

    public static function forbidden(string $message = 'Forbidden', array $errors = [], ?string $responseBody = null): self
    {
        return new self(
            $message,
            403,
            $errors,
            $responseBody
        );
    }

    public static function notFound(string $message = 'Resource not found', array $errors = [], ?string $responseBody = null): self
    {
        return new self(
            $message,
            404,
            $errors,
            $responseBody
        );
    }

    public static function unprocessableEntity(string $message = 'Validation failed', array $errors = [], ?string $responseBody = null): self
    {
        return new self(
            $message,
            422,
            $errors,
            $responseBody
        );
    }

    public static function tooManyRequests(string $message = 'Rate limit exceeded', array $errors = [], ?string $responseBody = null): self
    {
        return new self(
            $message,
            429,
            $errors,
            $responseBody
        );
    }

    public static function serverError(string $message = 'Internal server error', array $errors = [], ?string $responseBody = null): self
    {
        return new self(
            $message,
            500,
            $errors,
            $responseBody
        );
    }

    public static function serviceUnavailable(string $message = 'Service unavailable', array $errors = [], ?string $responseBody = null): self
    {
        return new self(
            $message,
            503,
            $errors,
            $responseBody
        );
    }

    public static function fromHttpStatus(int $statusCode, string $message = '', array $errors = [], ?string $responseBody = null): self
    {
        return match ($statusCode) {
            400 => self::badRequest($message ?: 'Bad Request', $errors, $responseBody),
            401 => self::unauthorized($message ?: 'Unauthorized', $errors, $responseBody),
            403 => self::forbidden($message ?: 'Forbidden', $errors, $responseBody),
            404 => self::notFound($message ?: 'Resource not found', $errors, $responseBody),
            422 => self::unprocessableEntity($message ?: 'Validation failed', $errors, $responseBody),
            429 => self::tooManyRequests($message ?: 'Rate limit exceeded', $errors, $responseBody),
            500 => self::serverError($message ?: 'Internal server error', $errors, $responseBody),
            503 => self::serviceUnavailable($message ?: 'Service unavailable', $errors, $responseBody),
            default => new self(
                $message ?: "HTTP {$statusCode} error",
                $statusCode,
                $errors,
                $responseBody
            )
        };
    }
} 