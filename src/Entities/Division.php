<?php

namespace WeblaborMx\World\Entities;

use Stringable;
use WeblaborMx\World\Entity;
use WeblaborMx\World\World;

class Division extends Entity implements Stringable
{
    protected ?Division $parent;

    /**
     * @var Division[]
     */
    protected array $children;

    public function __construct(
        public readonly int $id,
        public readonly ?string $name = null,
        public readonly ?string $country = null,
        public readonly ?string $a1code = null,
        public readonly ?string $level = null,
        public readonly ?int $population = null,
        public readonly ?float $lat = null,
        public readonly ?float $long = null,
        public readonly ?string $timezone = null,
        public readonly ?int $parent_id = null,
    ) {
    }

    /*
     * Endpoints
     */

    public function parent(): ?static
    {
        if (isset($this->parent)) {
            return $this->parent;
        }

        $result = $this->client->makeSafeCall(
            !is_null($this->parent_id)
                ? "/division/{$this->parent_id}"
                : "/division/{$this->id}/parent"
        );

        if (is_null($result)) {
            return $result;
        }

        $parent = self::fromJson($result);

        $parent->__setClient($this->client);

        $this->__setParent($parent);

        return $parent;
    }

    /**
     * @return Division[]
     */
    public function children(): array
    {
        if (isset($this->children)) {
            return $this->children;
        }

        $result = $this->client->makeSafeCall("/division/{$this->id}/children");

        if (!$result) {
            return [];
        }

        $children = array_map(
            fn ($v) => self::fromJson($v)
                ->__setParent($this)
                ->__setClient($this->client),
            $result
        );

        return $this->children = $children;
    }

    /*
     * Utils
     */

    public static function fromJson(array|string $json): static
    {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }

        return new static(
            id: $json['id'],
            name: $json['name'] ?? null,
            country: $json['country'] ?? null,
            a1code: $json['a1code'] ?? null,
            level: $json['level'] ?? null,
            population: $json['population'] ?? null,
            lat: $json['lat'] ?? null,
            long: $json['long'] ?? null,
            timezone: $json['timezone'] ?? null,
            parent_id: $json['parent_id'] ?? null,
        );
    }

    /**
     * Tries to get a division by ID. Returns null on error.
     */
    public static function get(int $id, ?string $search = null, ?array $fields = null): ?static
    {
        $result = World::getClient()
            ->makeSafeCall(
                "/division/{$id}",
                array_filter(compact('search', 'fields'))
            );

        if (is_null($result)) {
            return $result;
        }

        return self::fromJson($result)->__setClient(World::getClient());
    }

    public function __setParent(Division $division): self
    {
        $this->parent = $division;

        return $this;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
