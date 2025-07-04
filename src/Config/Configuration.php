<?php

namespace Iberbanco\SDK\Config;

class Configuration
{
    public const SANDBOX_URL = 'https://sandbox.api.iberbanco.finance/api/v2';
    public const PRODUCTION_URL = 'https://production.api.iberbancoltd.com/api/v2';

    private string $baseUrl;
    private string $username;
    private int $timeout;
    private bool $verifySSL;
    private array $defaultHeaders;
    private bool $debug;
    private bool $sandbox;

    public function __construct(array $config = [])
    {
        $this->sandbox = $config['sandbox'] ?? true;
        
        if (isset($config['base_url'])) {
            $this->baseUrl = $config['base_url'];
        } else {
            $this->baseUrl = $this->sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
        }
        
        $this->username = $config['username'] ?? '';
        $this->timeout = $config['timeout'] ?? 30;
        $this->verifySSL = $config['verify_ssl'] ?? true;
        $this->debug = $config['debug'] ?? false;
        $this->defaultHeaders = array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'Iberbanco-PHP-SDK/1.0.0'
        ], $config['headers'] ?? []);
    }

    public function getBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function setSandbox(bool $sandbox): self
    {
        $this->sandbox = $sandbox;
        $this->baseUrl = $sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
        return $this;
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

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function getVerifySSL(): bool
    {
        return $this->verifySSL;
    }

    public function setVerifySSL(bool $verifySSL): self
    {
        $this->verifySSL = $verifySSL;
        return $this;
    }

    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders;
    }

    public function setDefaultHeaders(array $headers): self
    {
        $this->defaultHeaders = $headers;
        return $this;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->defaultHeaders[$key] = $value;
        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    public static function fromEnvironment(): self
    {
        return new self([
            'base_url' => $_ENV['IBERBANCO_BASE_URL'] ?? null,
            'sandbox' => filter_var($_ENV['IBERBANCO_SANDBOX'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
            'username' => $_ENV['IBERBANCO_USERNAME'] ?? '',
            'timeout' => (int)($_ENV['IBERBANCO_TIMEOUT'] ?? 30),
            'verify_ssl' => filter_var($_ENV['IBERBANCO_VERIFY_SSL'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
            'debug' => filter_var($_ENV['IBERBANCO_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN)
        ]);
    }

    public function validate(): void
    {
        if (empty($this->baseUrl)) {
            throw new \InvalidArgumentException('Base URL is required');
        }

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid base URL format');
        }

        if ($this->timeout <= 0) {
            throw new \InvalidArgumentException('Timeout must be greater than 0');
        }
    }

    public function toArray(): array
    {
        return [
            'base_url' => $this->baseUrl,
            'sandbox' => $this->sandbox,
            'username' => $this->username,
            'timeout' => $this->timeout,
            'verify_ssl' => $this->verifySSL,
            'headers' => $this->defaultHeaders,
            'debug' => $this->debug
        ];
    }
} 