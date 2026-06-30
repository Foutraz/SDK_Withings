<?php

namespace Foutraz\Withings\Concerns;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Foutraz\Withings\Exceptions\ActionFailed;
use Foutraz\Withings\Exceptions\InvalidData;
use Foutraz\Withings\Exceptions\ResourceNotFound;
use Foutraz\Withings\Exceptions\TooManyRequestsException;
use Foutraz\Withings\Exceptions\Unauthorized;

trait MakesHttpRequests
{
    /**
     * @param  array<string, mixed>  $query
     *
     * @throws ActionFailed
     * @throws GuzzleException
     * @throws InvalidData
     * @throws ResourceNotFound
     * @throws TooManyRequestsException
     * @throws Unauthorized
     */
    public function get(string $uri, array $query = []): mixed
    {
        return $this->request('GET', $uri, [], $query);
    }

    /**
     * @throws ResourceNotFound
     * @throws Unauthorized
     * @throws GuzzleException
     * @throws ActionFailed
     * @throws InvalidData
     */
    public function post(string $uri, array $payload = [])
    {
        return $this->request('POST', $uri, $payload);
    }

    /**
     * @throws ResourceNotFound
     * @throws Unauthorized
     * @throws GuzzleException
     * @throws InvalidData
     * @throws ActionFailed
     */
    public function put(string $uri, array $payload = [])
    {
        return $this->request('PUT', $uri, $payload);
    }

    /**
     * @throws ResourceNotFound
     * @throws Unauthorized
     * @throws GuzzleException
     * @throws InvalidData
     * @throws ActionFailed
     */
    public function patch(string $uri, array $payload = [])
    {
        return $this->request('PATCH', $uri, $payload);
    }

    /**
     * @throws ResourceNotFound
     * @throws Unauthorized
     * @throws GuzzleException
     * @throws ActionFailed
     * @throws InvalidData
     */
    public function delete(string $uri, array $payload = [])
    {
        return $this->request('DELETE', $uri, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $query
     *
     * @throws ResourceNotFound
     * @throws Unauthorized
     * @throws GuzzleException
     * @throws ActionFailed
     * @throws InvalidData
     * @throws TooManyRequestsException
     */
    public function request(string $verb, string $uri, array $payload = [], array $query = []): mixed
    {
        $options = [];

        if (! empty($payload)) {
            $options['json'] = $payload;
        }

        if (! empty($query)) {
            $options['query'] = $query;
        }

        $response = $this->client->request($verb, $uri, $options);

        if (! $this->isSuccessful($response)) {
            $this->handleRequestError($response);
        }

        $responseBody = (string) $response->getBody();

        $decoded = json_decode($responseBody, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $responseBody;
    }

    public function isSuccessful(?ResponseInterface $response): bool
    {
        if (! $response) {
            return false;
        }

        return (int) substr((string) $response->getStatusCode(), 0, 1) === 2;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function buildFilterString(array $filters): string
    {
        if (count($filters) === 0) {
            return '';
        }

        $preparedFilters = [];
        foreach ($filters as $name => $value) {
            $preparedFilters["filter[$name]"] = $value;
        }

        return '?'.http_build_query($preparedFilters);
    }

    /**
     * @throws ActionFailed
     * @throws InvalidData
     * @throws ResourceNotFound
     * @throws TooManyRequestsException
     * @throws Unauthorized
     */
    protected function handleRequestError(ResponseInterface $response): void
    {
        if ($response->getStatusCode() === 422) {
            throw new InvalidData(json_decode((string) $response->getBody(), true));
        }

        if ($response->getStatusCode() === 404) {
            throw new ResourceNotFound;
        }

        if ($response->getStatusCode() === 429) {
            throw new TooManyRequestsException((string) $response->getBody());
        }

        if ($response->getStatusCode() === 401) {
            throw new Unauthorized((string) $response->getBody());
        }

        throw new ActionFailed((string) $response->getBody());
    }
}
