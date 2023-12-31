<?php

namespace WeblaborMx\World;

use Exception;

class Client
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $apiBase = 'https://world.weblabor/api',
    ) {
    }

    public function makeCall(
        string $endpoint,
        array $params = [],
        string $action = 'GET',
        array $body = [],
    ): array {
        $url = $this->parseUrl(rtrim($this->apiBase, '/') . '/' . trim($endpoint, '/'));

        $params = array_filter(array_map(fn ($v) => is_array($v) ? implode(',', $v) : $v, $params));
        $url = !empty($params) ? $url . '?' . http_build_query($params) : $url;

        $ch = curl_init($url);

        $actions = [
            'GET' => CURLOPT_HTTPGET,
            'POST' => CURLOPT_POST
        ];

        $action = $actions[strtoupper($action)] ?? 'GET';

        curl_setopt_array($ch, [
            $action => true,
            CURLOPT_TIMEOUT => 30,
            ...($action === 'GET' ? [] : [CURLOPT_POSTFIELDS => $body]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . trim($this->apiKey)
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_RETURNTRANSFER => true
        ]);

        $result = curl_exec($ch);

        curl_close($ch);

        if ($result === false) {
            $error = curl_error($ch);
            throw new Exception("Error on HTTP request to '$url' ($error)");
        }

        $response = [];

        $exception = new Exception("Error parsing response from '$url'");
        try {
            $response = json_decode($result, true);
        } catch (\Throwable $th) {
            throw $exception;
        }

        if (is_null($response)) {
            throw $exception;
        }

        return $response;
    }

    public function makeSafeCall(
        string $endpoint,
        array $params = [],
        string $action = 'GET',
        array $body = [],
    ): ?array {
        $result = null;
        try {
            $result = $this->makeCall($endpoint, $params, $action, $body);
        } finally {
            return $result;
        }
    }

    private function parseUrl($url)
    {
        $url = parse_url($url);
        $scheme = $url['scheme'] ?? 'https';
        $host = $url['host'];
        $path = $url['path'] ?? '/';
        $query = $url['query'] ?? null;

        $port = isset($url['port']) ? ":{$url['port']}" : null;

        $path = implode(
            '/',
            array_map(fn ($v) => rawurlencode($v), explode('/', $path))
        );

        if ($query) {
            $query = array_map(
                function ($v) {
                    $v = explode('=', trim($v));
                    if (isset($v[1])) {
                        $v[1] = rawurlencode($v[1]);
                    }
                    return implode('=', $v);
                },
                explode(',', $query)
            );
            $query = '?' . implode(',', $query);
        }

        return "{$scheme}://{$host}{$path}{$port}{$query}";
    }
}
