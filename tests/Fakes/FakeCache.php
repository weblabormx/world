<?php

namespace WeblaborMx\World\Tests\Fakes;

class FakeCache
{
    private static ?FakeCache $instance = null;

    public array $store = [];

    private function __construct()
    {
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = new self();
    }

    public function remember(string $key, mixed $ttl, callable $callback): mixed
    {
        if (!array_key_exists($key, $this->store)) {
            $this->store[$key] = $callback();
        }
        return $this->store[$key];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->store) ? $this->store[$key] : $default;
    }

    public function put(string $key, mixed $value, mixed $ttl = null): bool
    {
        $this->store[$key] = $value;
        return true;
    }

    public function forget(string $key): void
    {
        unset($this->store[$key]);
    }
}
