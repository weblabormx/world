<?php

namespace WeblaborMx\World\Tests\Unit\Division;

use PHPUnit\Framework\TestCase;
use WeblaborMx\World\Entities\Division;
use WeblaborMx\World\Tests\Fakes\FakeCache;
use WeblaborMx\World\Tests\Fakes\FakeClient;
use WeblaborMx\World\World;

class CountriesTest extends TestCase
{
    private FakeClient $client;

    private static array $mexicoData = [
        'id' => 1,
        'name' => 'Mexico',
        'country' => 'MX',
        'a1code' => null,
        'level' => 'country',
        'population' => 130000000,
        'lat' => 23.6345,
        'long' => -102.5528,
        'timezone' => 'America/Mexico_City',
        'parent_id' => null,
    ];

    protected function setUp(): void
    {
        FakeCache::reset();
        $this->client = new FakeClient();
        World::setClient($this->client);
    }

    public function test_returns_correct_data_when_api_responds(): void
    {
        $this->client->addResponse([self::$mexicoData]);

        $result = Division::countries();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Division::class, $result[0]);
        $this->assertSame('Mexico', $result[0]->name);
        $this->assertSame('MX', $result[0]->country);
    }

    public function test_successful_response_is_cached(): void
    {
        $this->client->addResponse([self::$mexicoData]);

        Division::countries();

        $cache = FakeCache::instance();
        $this->assertArrayHasKey('wdivision_countries_no-fields', $cache->store);
        $this->assertArrayHasKey('wdivision_countries_no-fields_fallback', $cache->store);
    }

    public function test_uses_cache_and_avoids_api_call_when_cache_is_valid(): void
    {
        $this->client->addResponse([self::$mexicoData]);
        Division::countries();

        $callsAfterFirstRequest = $this->client->callCount;

        $result = Division::countries();

        $this->assertSame($callsAfterFirstRequest, $this->client->callCount);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function test_uses_stale_cache_when_api_fails_and_fallback_exists(): void
    {
        $cachedDivision = Division::fromJson(self::$mexicoData)->__setClient($this->client);
        $cache = FakeCache::instance();
        $cache->put('wdivision_countries_no-fields_fallback', [$cachedDivision]);

        $result = Division::countries();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame('Mexico', $result[0]->name);
    }

    public function test_returns_empty_array_when_api_fails_and_no_cache_exists(): void
    {
        $result = Division::countries();

        $this->assertSame([], $result);
    }
}
