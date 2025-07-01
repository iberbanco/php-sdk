<?php

namespace Iberbanco\SDK\Http;

use Iberbanco\SDK\Exceptions\ApiException;

interface HttpClientInterface
{
    public function get(string $uri, array $headers = [], array $options = []): array;

    public function post(string $uri, $data = [], array $headers = [], array $options = []): array;

    public function put(string $uri, $data = [], array $headers = [], array $options = []): array;

    public function delete(string $uri, array $headers = [], array $options = []): array;

    public function patch(string $uri, $data = [], array $headers = [], array $options = []): array;

    public function setBaseUrl(string $baseUrl): self;

    public function setDefaultHeaders(array $headers): self;

    public function setTimeout(int $timeout): self;

    public function setVerifySSL(bool $verify): self;

    public function setDebug(bool $debug): self;
} 