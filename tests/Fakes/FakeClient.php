<?php

namespace WeblaborMx\World\Tests\Fakes;

use Exception;
use WeblaborMx\World\Client;

class FakeClient extends Client
{
    private array $responses = [];

    public int $callCount = 0;

    public function __construct()
    {
        parent::__construct(apiKey: 'fake-key');
    }

    public function addResponse(?array $response): void
    {
        $this->responses[] = $response;
    }

    public function makeSafeCall(string $endpoint, array $params = [], string $action = 'GET', array $body = []): ?array
    {
        $this->callCount++;
        if (empty($this->responses)) {
            return null;
        }
        return array_shift($this->responses);
    }

    public function makeCall(string $endpoint, array $params = [], string $action = 'GET', array $body = []): array
    {
        $result = $this->makeSafeCall($endpoint, $params, $action, $body);
        if (is_null($result)) {
            throw new Exception("FakeClient: no response queued for '$endpoint'");
        }
        return $result;
    }
}
