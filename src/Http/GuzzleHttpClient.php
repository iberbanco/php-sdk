<?php

namespace Iberbanco\SDK\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Iberbanco\SDK\Exceptions\ApiException;
use Iberbanco\SDK\Constants\ApiConstants;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private Client $client;
    private string $baseUrl = '';
    private array $defaultHeaders = [];
    private int $timeout = ApiConstants::DEFAULT_TIMEOUT;
    private bool $verifySSL = true;
    private bool $debug = false;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    public function get(string $uri, array $headers = [], array $options = []): array
    {
        return $this->makeRequest('GET', $uri, null, $headers, $options);
    }

    public function post(string $uri, $data = [], array $headers = [], array $options = []): array
    {
        return $this->makeRequest('POST', $uri, $data, $headers, $options);
    }

    public function put(string $uri, $data = [], array $headers = [], array $options = []): array
    {
        return $this->makeRequest('PUT', $uri, $data, $headers, $options);
    }

    public function delete(string $uri, array $headers = [], array $options = []): array
    {
        return $this->makeRequest('DELETE', $uri, null, $headers, $options);
    }

    public function patch(string $uri, $data = [], array $headers = [], array $options = []): array
    {
        return $this->makeRequest('PATCH', $uri, $data, $headers, $options);
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    public function setDefaultHeaders(array $headers): self
    {
        $this->defaultHeaders = $headers;
        return $this;
    }

    public function setTimeout(int $timeout): self
    {
        if ($timeout <= 0 || $timeout > ApiConstants::MAX_TIMEOUT) {
            throw new \InvalidArgumentException(
                "Timeout must be between 1 and " . ApiConstants::MAX_TIMEOUT . " seconds"
            );
        }
        
        $this->timeout = $timeout;
        return $this;
    }

    public function setVerifySSL(bool $verify): self
    {
        $this->verifySSL = $verify;
        return $this;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    private function makeRequest(string $method, string $uri, $data = null, array $headers = [], array $options = []): array
    {
        try {
            $url = $this->buildUrl($uri);
            $requestOptions = $this->buildRequestOptions($data, $headers, $options);

            if ($this->debug) {
                error_log("Making {$method} request to: {$url}");
                error_log("Request options: " . json_encode($requestOptions, JSON_PRETTY_PRINT));
            }

            $response = $this->client->request($method, $url, $requestOptions);

            return $this->processResponse($response);

        } catch (ConnectException $e) {
            throw ApiException::networkError($e->getMessage(), $e);
        } catch (ClientException $e) {
            throw $this->handleClientException($e);
        } catch (ServerException $e) {
            throw $this->handleServerException($e);
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (\Exception $e) {
            throw new ApiException(
                'Unexpected error: ' . $e->getMessage(),
                0,
                [],
                null,
                null,
                $e
            );
        }
    }

    private function buildUrl(string $uri): string
    {
        $uri = ltrim($uri, '/');
        
        if (empty($this->baseUrl)) {
            return $uri;
        }

        return $this->baseUrl . '/' . $uri;
    }

    private function buildRequestOptions($data, array $headers, array $options): array
    {
        $requestOptions = array_merge([
            RequestOptions::TIMEOUT => $this->timeout,
            RequestOptions::VERIFY => $this->verifySSL,
            RequestOptions::HEADERS => array_merge($this->defaultHeaders, $headers),
            RequestOptions::HTTP_ERRORS => false, // We handle errors manually
        ], $options);

        if ($data !== null) {
            if (is_array($data)) {
                $requestOptions[RequestOptions::JSON] = $data;
            } else {
                $requestOptions[RequestOptions::BODY] = $data;
            }
        }

        return $requestOptions;
    }

    private function processResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        if ($this->debug) {
            error_log("Response status: {$statusCode}");
            error_log("Response body: {$body}");
        }

        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(
                'Invalid JSON response: ' . json_last_error_msg(),
                $statusCode,
                ['JSON parsing error'],
                $body
            );
        }

        if ($statusCode >= 400) {
            $message = $data['message'] ?? 'API request failed';
            $errors = $data['errors'] ?? [];
            
            throw ApiException::fromHttpStatus($statusCode, $message, $errors, $body);
        }

        return $data;
    }

    private function handleClientException(ClientException $e): ApiException
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        
        $data = json_decode($body, true);
        $message = $data['message'] ?? $e->getMessage();
        $errors = $data['errors'] ?? [];

        return ApiException::fromHttpStatus($statusCode, $message, $errors, $body);
    }

    private function handleServerException(ServerException $e): ApiException
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        
        $data = json_decode($body, true);
        $message = $data['message'] ?? 'Server error occurred';
        $errors = $data['errors'] ?? [];

        return ApiException::fromHttpStatus($statusCode, $message, $errors, $body);
    }

    private function handleRequestException(RequestException $e): ApiException
    {
        if ($e->hasResponse()) {
            return $this->handleResponseException($e);
        }

        if (strpos($e->getMessage(), 'timed out') !== false) {
            return ApiException::timeout($this->timeout);
        }

        return ApiException::networkError($e->getMessage(), $e);
    }

    private function handleResponseException(RequestException $e): ApiException
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        
        $data = json_decode($body, true);
        $message = $data['message'] ?? $e->getMessage();
        $errors = $data['errors'] ?? [];

        return ApiException::fromHttpStatus($statusCode, $message, $errors, $body);
    }
} 