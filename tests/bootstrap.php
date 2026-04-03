<?php

use WeblaborMx\World\Tests\Fakes\FakeCache;

require_once __DIR__ . '/../vendor/autoload.php';

function cache(): FakeCache
{
    return FakeCache::instance();
}

function now(): object
{
    return new class {
        public function addDay(): int
        {
            return 86400;
        }
    };
}
