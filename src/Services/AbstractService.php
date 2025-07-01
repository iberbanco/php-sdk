<?php

namespace Iberbanco\SDK\Services;

use Iberbanco\SDK\Auth\Authentication;
use Iberbanco\SDK\Http\HttpClientInterface;
use Iberbanco\SDK\Exceptions\ApiException;
use Iberbanco\SDK\Exceptions\AuthenticationException;

abstract class AbstractService
{
    protected HttpClientInterface $httpClient;
    protected Authentication $auth;

    public function __construct(HttpClientInterface $httpClient, Authentication $auth)
    {
        $this->httpClient = $httpClient;
        $this->auth = $auth;
    }

    protected function get(string $uri, array $query = [], array $headers = []): array
    {
        $headers = $this->addAuthHeaders($headers);
        
        if (!empty($query)) {
            $uri .= '?' . http_build_query($query);
        }

        return $this->httpClient->get($uri, $headers);
    }

    protected function post(string $uri, array $data = [], array $headers = []): array
    {
        $headers = $this->addAuthHeaders($headers);
        return $this->httpClient->post($uri, $data, $headers);
    }

    protected function put(string $uri, array $data = [], array $headers = []): array
    {
        $headers = $this->addAuthHeaders($headers);
        return $this->httpClient->put($uri, $data, $headers);
    }

    protected function delete(string $uri, array $headers = []): array
    {
        $headers = $this->addAuthHeaders($headers);
        return $this->httpClient->delete($uri, $headers);
    }

    protected function patch(string $uri, array $data = [], array $headers = []): array
    {
        $headers = $this->addAuthHeaders($headers);
        return $this->httpClient->patch($uri, $data, $headers);
    }

    protected function addAuthHeaders(array $headers = []): array
    {
        if (!$this->auth->getToken()) {
            throw AuthenticationException::missingToken(['Authentication token is required for this request']);
        }

        $authHeaders = $this->auth->generateAuthHeaders();
        return array_merge($headers, $authHeaders);
    }

    protected function validateRequired(array $data, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                throw new \InvalidArgumentException("Required field '{$field}' is missing or empty");
            }
        }
    }

    protected function buildQuery(array $params): string
    {
        $filtered = array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });

        return http_build_query($filtered);
    }

    protected function extractData(array $response): array
    {
        return $response['data'] ?? $response;
    }

    protected function extractPagination(array $response): ?array
    {
        return $response['meta']['pagination'] ?? null;
    }

    protected function isSuccessfulResponse(array $response): bool
    {
        return isset($response['status']) && $response['status'] === 'success';
    }

    protected function formatAmount($amount): float
    {
        return round((float)$amount, 2);
    }

    protected function formatCurrency($currency)
    {
        if (is_string($currency)) {
            return strtoupper($currency);
        }
        
        return $currency;
    }

    protected function formatDate($date): string
    {
        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d');
        }
        
        if (is_string($date)) {
            $dateTime = new \DateTime($date);
            return $dateTime->format('Y-m-d');
        }
        
        throw new \InvalidArgumentException('Invalid date format');
    }
} 