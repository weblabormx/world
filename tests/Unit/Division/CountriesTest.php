<?php

namespace WeblaborMx\World\Tests\Unit\Division;

use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase;
use WeblaborMx\World\Entities\Division;
use WeblaborMx\World\World;

class CountriesTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $root = dirname(__DIR__, 3);
        if (file_exists($root . '/.env')) {
            \Dotenv\Dotenv::createMutable($root)->safeLoad();
        }
        $app['config']->set('cache.default', 'array');
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function apiKey(): ?string
    {
        return env('WORLD_API_KEY') ?: null;
    }

    /**
     * Client is never initialized — World::getClient() throws internally.
     * Division::countries() must catch it and return [].
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_returns_empty_array_when_client_is_not_configured(): void
    {
        $result = Division::countries();

        $this->assertSame([], $result);
    }

    public function test_returns_empty_array_with_invalid_api_key(): void
    {
        World::init('invalid-api-key');

        $result = Division::countries();

        $this->assertSame([], $result);
    }

    public function test_returns_countries_with_valid_api_key(): void
    {
        if (!$this->apiKey()) {
            $this->markTestSkipped('WORLD_API_KEY not set in .env');
        }
        World::init($this->apiKey());

        $result = Division::countries();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(Division::class, $result[0]);
    }

    public function test_successful_response_populates_both_cache_keys(): void
    {
        if (!$this->apiKey()) {
            $this->markTestSkipped('WORLD_API_KEY not set in .env');
        }
        World::init($this->apiKey());

        Division::countries();

        $this->assertTrue(Cache::has('wdivision_countries_no-fields'));
        $this->assertTrue(Cache::has('wdivision_countries_no-fields_fallback'));
    }

    public function test_returns_from_cache_without_hitting_api_again(): void
    {
        if (!$this->apiKey()) {
            $this->markTestSkipped('WORLD_API_KEY not set in .env');
        }
        World::init($this->apiKey());
        $first = Division::countries();

        // Switch to invalid key — second call must serve from cache
        World::init('invalid-api-key');
        $second = Division::countries();

        $this->assertCount(count($first), $second);
        $this->assertSame($first[0]->name, $second[0]->name);
    }

    public function test_returns_fallback_when_fresh_key_expires_and_api_fails(): void
    {
        if (!$this->apiKey()) {
            $this->markTestSkipped('WORLD_API_KEY not set in .env');
        }
        World::init($this->apiKey());
        $original = Division::countries();

        Cache::forget('wdivision_countries_no-fields');
        World::init('invalid-api-key');

        $result = Division::countries();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertSame($original[0]->name, $result[0]->name);
    }
}
