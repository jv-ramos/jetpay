<?php

namespace App\Services\Gateway;

use Illuminate\Support\Facades\Http;

class GatewayRequestService
{
    private string $baseUrl;
    private string $method = 'get';
    private array $data = [];
    private array $headers = [];
    private ?string $token = null;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function post(): static
    {
        $this->method = 'post';
        return $this;
    }

    public function get(): static
    {
        $this->method = 'get';
        return $this;
    }

    public function withToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function withHeaders(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    public function send(string $endpoint, array $data = []): array
    {
        $http = Http::withHeaders($this->headers);

        if ($this->token) {
            $http = $http->withToken($this->token);
        }

        $response = count($data) > 0
            ? $http->{$this->method}("{$this->baseUrl}{$endpoint}", $data)
            : $http->{$this->method}("{$this->baseUrl}{$endpoint}");

        return $response->json();
    }
}
