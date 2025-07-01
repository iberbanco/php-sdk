<?php

namespace Iberbanco\SDK\Exceptions;

use Exception;

class IberbancoException extends Exception
{
    protected array $errors = [];
    protected ?array $responseData = null;

    public function __construct(
        string $message = '',
        int $code = 0,
        array $errors = [],
        ?array $responseData = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
        $this->responseData = $responseData;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    public function setResponseData(?array $responseData): self
    {
        $this->responseData = $responseData;
        return $this;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getFormattedMessage(): string
    {
        $message = $this->getMessage();
        
        if ($this->hasErrors()) {
            $message .= ' Errors: ' . implode(', ', $this->errors);
        }
        
        return $message;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'errors' => $this->errors,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'response_data' => $this->responseData
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
} 