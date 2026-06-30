<?php

namespace Foutraz\Withings;

use Foutraz\Withings\Actions\ManagesAuthentication;
use Foutraz\Withings\Actions\ManagesMeasurements;
use Foutraz\Withings\Concerns\MakesHttpRequests;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class WithingsManager
{
    use MakesHttpRequests;

    public function __construct(
        public string $endpoint,
        public string $apiToken,
        public string $clientId,
        public string $clientSecret,
        public string $redirectUri,
        public ?ClientInterface $client = null
    ) {
        $this->client ??= new Client([
            'http_errors' => false,
            'base_uri' => rtrim($this->endpoint, '/').'/',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->apiToken,
            ],
        ]);
    }

    public function auth(): ManagesAuthentication
    {
        return new ManagesAuthentication($this->endpoint, $this->apiToken, $this->clientId, $this->clientSecret, $this->redirectUri, $this->client);
    }

    public function measurements(): ManagesMeasurements
    {
        return new ManagesMeasurements($this->endpoint, $this->apiToken, $this->clientId, $this->clientSecret, $this->redirectUri, $this->client);
    }

    public function setToken(string $token): static
    {
        $this->apiToken = $token;

        $this->client = new Client([
            'http_errors' => false,
            'base_uri' => rtrim($this->endpoint, '/').'/',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return $this;
    }
}
