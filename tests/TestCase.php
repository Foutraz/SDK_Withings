<?php

namespace Foutraz\Withings\Tests;

use Foutraz\Withings\WithingsManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var array<int, array<string, mixed>> */
    protected array $history = [];

    /** @param array<int, Response> $responses */
    protected function managerWithResponses(array $responses): WithingsManager
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        $client = new Client([
            'handler' => $stack,
            'http_errors' => false,
            'base_uri' => 'https://wbsapi.withings.net/',
        ]);

        return new WithingsManager(
            'https://wbsapi.withings.net',
            'access-token',
            'client-id',
            'client-secret',
            'https://example.test/callback',
            $client,
        );
    }

    /** @param array<mixed> $body */
    protected function jsonResponse(int $status, array $body): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], (string) json_encode($body));
    }

    /** @return list<array<string, string>> */
    protected function requestBodies(): array
    {
        return array_map(function (array $transaction): array {
            parse_str((string) $transaction['request']->getBody(), $fields);

            return $fields;
        }, $this->history);
    }
}
