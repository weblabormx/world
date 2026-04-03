<?php

namespace WeblaborMx\World\Tests\Unit\Division;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;
use Orchestra\Testbench\TestCase;
use ReflectionClass;
use WeblaborMx\World\Entities\Division;
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

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('cache.default', 'array');
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->client = new FakeClient();
        $property = (new ReflectionClass(World::class))->getProperty('client');
        $property->setValue(null, $this->client);
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

        $this->assertTrue(Cache::has('wdivision_countries_no-fields'));
        $this->assertTrue(Cache::has('wdivision_countries_no-fields_fallback'));
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

    public function test_uses_fallback_cache_when_fresh_key_expires_and_api_fails(): void
    {
        $this->client->addResponse([self::$mexicoData]);
        Division::countries();

        // Simulate the fresh key expiring (fallback key survives with no TTL)
        Cache::forget('wdivision_countries_no-fields');
        $callsBefore = $this->client->callCount;

        $result = Division::countries();

        // A remote attempt was made (fresh key was gone), it failed, then the fallback was used
        $this->assertSame($callsBefore + 1, $this->client->callCount);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame('Mexico', $result[0]->name);
    }

    public function test_returns_empty_array_when_api_fails_and_no_cache_exists(): void
    {
        $result = Division::countries();

        $this->assertSame([], $result);
    }

    public function test_returns_empty_array_when_client_not_configured_and_no_cache_exists(): void
    {
        Log::shouldReceive('warning')->once()->with(
            Mockery::on(function ($message) {
                return str_contains($message, 'not configured');
            })
        );
        $this->client->addException(new Exception('Weblabor World API key not set.'));

        $result = Division::countries();

        $this->assertSame([], $result);
    }

    public function test_returns_fallback_cache_when_client_not_configured(): void
    {
        $this->client->addResponse([self::$mexicoData]);
        Division::countries();
        Cache::forget('wdivision_countries_no-fields');

        Log::shouldReceive('warning')->once()->with(
            Mockery::on(function ($message) {
                return str_contains($message, 'not configured');
            })
        );
        $this->client->addException(new Exception('Weblabor World API key not set.'));

        $result = Division::countries();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame('Mexico', $result[0]->name);
    }
}
