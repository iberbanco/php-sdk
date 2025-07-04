<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables if available
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Test configuration defaults
define('IBERBANCO_TEST_CONFIG', [
    'sandbox' => true,
    'username' => $_ENV['IBERBANCO_USERNAME'] ?? 'test_agent',
    'timeout' => 30,
    'verify_ssl' => false,
    'debug' => true
]);

class TestConfig
{
    public static function getDefaultConfig(): array
    {
        return IBERBANCO_TEST_CONFIG;
    }

    public static function getSandboxUrl(): string
    {
        return 'https://sandbox.api.iberbanco.finance/api/v2';
    }

    public static function getTestUsername(): string
    {
        return IBERBANCO_TEST_CONFIG['username'];
    }

    public static function getConfig(): array
    {
        return IBERBANCO_TEST_CONFIG;
    }

    public static function getMockHttpClient(): \Iberbanco\SDK\Http\HttpClientInterface
    {
        return new class implements \Iberbanco\SDK\Http\HttpClientInterface {
            private array $responses = [];
            private array $requests = [];

            public function get(string $uri, array $headers = [], array $options = []): array
            {
                $this->requests[] = ['method' => 'GET', 'uri' => $uri, 'headers' => $headers, 'options' => $options];
                return $this->getResponse('GET', $uri);
            }

            public function post(string $uri, $data = [], array $headers = [], array $options = []): array
            {
                $this->requests[] = ['method' => 'POST', 'uri' => $uri, 'data' => $data, 'headers' => $headers, 'options' => $options];
                return $this->getResponse('POST', $uri);
            }

            public function put(string $uri, $data = [], array $headers = [], array $options = []): array
            {
                $this->requests[] = ['method' => 'PUT', 'uri' => $uri, 'data' => $data, 'headers' => $headers, 'options' => $options];
                return $this->getResponse('PUT', $uri);
            }

            public function delete(string $uri, array $headers = [], array $options = []): array
            {
                $this->requests[] = ['method' => 'DELETE', 'uri' => $uri, 'headers' => $headers, 'options' => $options];
                return $this->getResponse('DELETE', $uri);
            }

            public function patch(string $uri, $data = [], array $headers = [], array $options = []): array
            {
                $this->requests[] = ['method' => 'PATCH', 'uri' => $uri, 'data' => $data, 'headers' => $headers, 'options' => $options];
                return $this->getResponse('PATCH', $uri);
            }

            public function setBaseUrl(string $baseUrl): self
            {
                return $this;
            }

            public function setDefaultHeaders(array $headers): self
            {
                return $this;
            }

            public function setTimeout(int $timeout): self
            {
                return $this;
            }

            public function setVerifySSL(bool $verify): self
            {
                return $this;
            }

            public function setDebug(bool $debug): self
            {
                return $this;
            }

            public function setResponse(string $method, string $uri, array $response): void
            {
                $this->responses["{$method}:{$uri}"] = $response;
            }

            public function getRequests(): array
            {
                return $this->requests;
            }

            public function clearRequests(): void
            {
                $this->requests = [];
            }

            private function getResponse(string $method, string $uri): array
            {
                $key = "{$method}:{$uri}";
                return $this->responses[$key] ?? [
                    'status' => 'success',
                    'message' => 'Mock response',
                    'data' => []
                ];
            }
        };
    }
} 