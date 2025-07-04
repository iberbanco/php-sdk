<?php

namespace Iberbanco\SDK;

use Iberbanco\SDK\Auth\Authentication;
use Iberbanco\SDK\Config\Configuration;
use Iberbanco\SDK\Http\GuzzleHttpClient;
use Iberbanco\SDK\Http\HttpClientInterface;
use Iberbanco\SDK\Services\AuthService;
use Iberbanco\SDK\Services\UserService;
use Iberbanco\SDK\Services\AccountService;
use Iberbanco\SDK\Services\TransactionService;
use Iberbanco\SDK\Services\CryptoTransactionService;
use Iberbanco\SDK\Services\CardService;
use Iberbanco\SDK\Services\ExportService;
use Iberbanco\SDK\Services\ExchangeService;

class IberbancoClient
{
    private Configuration $config;
    private HttpClientInterface $httpClient;
    private Authentication $auth;
    
    private ?AuthService $authService = null;
    private ?UserService $userService = null;
    private ?AccountService $accountService = null;
    private ?TransactionService $transactionService = null;
    private ?CryptoTransactionService $cryptoTransactionService = null;
    private ?CardService $cardService = null;
    private ?ExportService $exportService = null;
    private ?ExchangeService $exchangeService = null;

    public function __construct(Configuration $config, ?HttpClientInterface $httpClient = null)
    {
        $this->config = $config;
        $this->config->validate();
        
        $this->httpClient = $httpClient ?: new GuzzleHttpClient();
        $this->setupHttpClient();
        
        $this->auth = new Authentication($this->config->getUsername());
    }

    public static function create(array $config): self
    {
        return new self(new Configuration($config));
    }

    public static function createFromEnvironment(): self
    {
        return new self(Configuration::fromEnvironment());
    }

    public function auth(): AuthService
    {
        if ($this->authService === null) {
            $this->authService = new AuthService($this->httpClient, $this->auth);
        }

        return $this->authService;
    }

    public function users(): UserService
    {
        if ($this->userService === null) {
            $this->userService = new UserService($this->httpClient, $this->auth);
        }

        return $this->userService;
    }

    public function accounts(): AccountService
    {
        if ($this->accountService === null) {
            $this->accountService = new AccountService($this->httpClient, $this->auth);
        }

        return $this->accountService;
    }

    public function transactions(): TransactionService
    {
        if ($this->transactionService === null) {
            $this->transactionService = new TransactionService($this->httpClient, $this->auth);
        }

        return $this->transactionService;
    }

    public function cryptoTransactions(): CryptoTransactionService
    {
        if ($this->cryptoTransactionService === null) {
            $this->cryptoTransactionService = new CryptoTransactionService($this->httpClient, $this->auth);
        }

        return $this->cryptoTransactionService;
    }

    public function cards(): CardService
    {
        if ($this->cardService === null) {
            $this->cardService = new CardService($this->httpClient, $this->auth);
        }

        return $this->cardService;
    }

    public function export(): ExportService
    {
        if ($this->exportService === null) {
            $this->exportService = new ExportService($this->httpClient, $this->auth);
        }

        return $this->exportService;
    }

    public function exchange(): ExchangeService
    {
        if ($this->exchangeService === null) {
            $this->exchangeService = new ExchangeService($this->httpClient, $this->auth);
        }

        return $this->exchangeService;
    }

    public function setAuthToken(string $token): self
    {
        $this->auth->setToken($token);
        return $this;
    }

    public function getAuthToken(): ?string
    {
        return $this->auth->getToken();
    }

    public function isAuthenticated(): bool
    {
        return $this->auth->getToken() !== null;
    }

    public function getConfig(): Configuration
    {
        return $this->config;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    public function getAuth(): Authentication
    {
        return $this->auth;
    }

    public function updateConfig(array $config): self
    {
        foreach ($config as $key => $value) {
            switch ($key) {
                case 'sandbox':
                    $this->config->setSandbox($value);
                    break;
                case 'username':
                    $this->config->setUsername($value);
                    $this->auth->setUsername($value);
                    break;
                case 'timeout':
                    $this->config->setTimeout($value);
                    break;
                case 'verify_ssl':
                    $this->config->setVerifySSL($value);
                    break;
                case 'debug':
                    $this->config->setDebug($value);
                    break;
                case 'headers':
                    $this->config->setDefaultHeaders($value);
                    break;
            }
        }

        $this->setupHttpClient();
        return $this;
    }

    public function enableDebug(): self
    {
        $this->config->setDebug(true);
        $this->httpClient->setDebug(true);
        return $this;
    }

    public function disableDebug(): self
    {
        $this->config->setDebug(false);
        $this->httpClient->setDebug(false);
        return $this;
    }

    private function setupHttpClient(): void
    {
        $this->httpClient
            ->setBaseUrl($this->config->getBaseUrl())
            ->setDefaultHeaders($this->config->getDefaultHeaders())
            ->setTimeout($this->config->getTimeout())
            ->setVerifySSL($this->config->getVerifySSL())
            ->setDebug($this->config->isDebug());
    }

    public function authenticate(string $username, string $password): array
    {
        $response = $this->auth()->authenticate($username, $password);
        
        if (isset($response['data']['token'])) {
            $this->setAuthToken($response['data']['token']);
        }
        
        return $response;
    }

    public static function getVersion(): string
    {
        return '1.0.0';
    }

    public static function getInfo(): array
    {
        return [
            'name' => 'Iberbanco PHP SDK',
            'version' => self::getVersion(),
            'description' => 'Official PHP SDK for Iberbanco API v2',
            'author' => 'Iberbanco Ltd',
            'homepage' => 'https://iberbancoltd.com/',
            'php_version' => PHP_VERSION,
            'user_agent' => 'Iberbanco-PHP-SDK/' . self::getVersion()
        ];
    }
} 