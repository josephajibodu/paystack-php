<?php

namespace JosephAjibodu\Paystack;

use GuzzleHttp\Client;
use JosephAjibodu\Paystack\Resources\PlanResource;

class Paystack
{
    private string $secretKey;

    private Client $client;

    public function __construct(string $secretKey, bool $isLive = true)
    {
        $this->secretKey = $secretKey;

        $baseUri = $isLive ? 'https://api.paystack.co/' : 'https://api.paystack.test/';
        $this->client = new Client([
            'base_uri' => $baseUri,
            'timeout' => 30.0,
            'headers' => [
                'Authorization' => "Bearer {$this->secretKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function customer(): PlanResource
    {
        return new PlanResource($this->client);
    }
}
