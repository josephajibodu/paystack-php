<?php

namespace JosephAjibodu\Paystack\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JosephAjibodu\Paystack\Exceptions\ApiConnectionException;
use JosephAjibodu\Paystack\Exceptions\AuthenticationException;
use JosephAjibodu\Paystack\Exceptions\InvalidRequestException;
use JosephAjibodu\Paystack\Exceptions\PaystackException;
use JosephAjibodu\Paystack\Exceptions\RateLimitException;
use JosephAjibodu\Paystack\Paystack;
use Psr\Http\Message\ResponseInterface;

class ApiResource
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @throws PaystackException
     */
    protected function request(string $method, string $endpoint, array $params = []): array
    {
        try {
            $options = $this->buildRequestOptions($method, $params);
            $response = $this->client->request($method, $endpoint, $options);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            throw $this->handleRequestException($e);
        }
    }

    private function buildRequestOptions(string $method, array $params): array
    {
        return in_array($method, ['POST', 'PUT', 'PATCH'])
            ? ['json' => $params]
            : ['query' => $params];
    }

    /**
     * @throws PaystackException
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PaystackException("Invalid JSON response from API");
        }

        if ($data['status'] === false) {
            $this->createExceptionFromResponse($data, $response->getStatusCode());
        }

        return $data;
    }

    private function handleRequestException(GuzzleException $e): PaystackException
    {
        if ($e instanceof ConnectException) {
            return new ApiConnectionException("Network error: " . $e->getMessage(), 0, $e);
        }

        if ($e instanceof RequestException && $e->hasResponse()) {
            $response = $e->getResponse();
            $body = json_decode((string) $response->getBody(), true);
            return $this->createExceptionFromResponse($body, $response->getStatusCode());
        }

        return new PaystackException("An unexpected error occurred: " . $e->getMessage(), 0, $e);
    }

    private function createExceptionFromResponse(array $data, int $statusCode): PaystackException
    {
        $message = $data['message'] ?? 'Unknown error';

        return match ($statusCode) {
            400 => new InvalidRequestException($message, $statusCode),
            401 => new AuthenticationException($message, $statusCode),
            429 => new RateLimitException($message, $statusCode),
            default => new PaystackException($message, $statusCode),
        };
    }
}