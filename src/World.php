<?php

namespace WeblaborMx\World;

use Exception;

class World
{
    private static string $apiBase = 'https://world.weblabor/api';
    private static Client $client;

    public static function init(
        string $apiKey,
    ) {
        self::$client = new Client(
            apiKey: $apiKey,
            apiBase: self::$apiBase
        );
    }

    public static function getClient()
    {
        if (!isset(self::$client)) {
            throw new Exception('Weblabor World API key not set.');
        }

        return self::$client;
    }

    public static function getApiKey()
    {
        if (!isset(self::$client)) {
            throw new Exception('Weblabor World API key not set.');
        }

        return self::$client->apiKey;
    }

    public static function setApiBase($apiBase)
    {
        self::$apiBase = $apiBase;
    }

    public static function getApiBase()
    {
        if (isset(self::$client)) {
            return self::$client->apiBase;
        }

        return self::$apiBase;
    }
}
