<?php

namespace WeblaborMx\World\Entities;

use InvalidArgumentException;
use Stringable;
use WeblaborMx\World\Entity;
use WeblaborMx\World\World;

/**
 * @method self __setClient(\WeblaborMx\World\Client $client)
 */
class Division extends Entity implements Stringable
{
    protected ?Division $parent;

    /**
     * @var Division[]
     */
    protected array $children;

    public function __construct(
        public int $id,
        public ?string $name = null,
        public ?string $country = null,
        public ?string $a1code = null,
        public ?string $level = null,
        public ?int $population = null,
        public ?float $lat = null,
        public ?float $long = null,
        public ?string $timezone = null,
        public ?int $parent_id = null,
    ) {
    }

    /*
     * Endpoints
     */

    /**
     * Tries to get a division by ID
     */
    public static function get(int $id, ?string $search = null, ?array $fields = null): ?static
    {
        $result = World::safeCall(
            "/division/{$id}",
            array_filter(compact('search', 'fields'))
        );

        if (is_null($result)) {
            return $result;
        }

        return self::fromJson($result)->__setClient(World::getClient());
    }

    public static function getChildren(int $id, ?array $fields = null): ?array
    {
        $children = (new self($id))
            ->__setClient(World::getClient())
            ->children($fields);

        if (is_null($children)) {
            return $children;
        }

        return array_map(
            fn (Division $v) => $v->__setParent(null),
            $children
        );
    }

    public static function getParent(int $id, ?array $fields = null): ?static
    {
        return (new self($id))
            ->__setClient(World::getClient())
            ->parent($fields);
    }

    public static function search(string $search, int|Division|null $parent = null, ?array $fields = null)
    {
        if ($parent instanceof Division) {
            $parent = $parent->id;
        }

        $result = World::safeCall(
            implode('/', ['/search', $search, $parent]),
            array_filter(compact('fields'))
        );

        if (is_null($result)) {
            return $result;
        }

        return array_map(
            fn (array $v) => self::fromJson($v)->__setClient(World::getClient()),
            $result
        );
    }

    public static function country(string $code, ?array $fields = null): ?static
    {
        $result = World::safeCall(
            "/country/" . strtoupper($code),
            array_filter(compact('fields'))
        );

        if (is_null($result)) {
            return $result;
        }

        return self::fromJson($result)->__setClient(World::getClient());
    }

    /**
     * Returns an array of all countries
     * 
     * @return Division[]|null
     */
    public static function countries(?array $fields = null): ?array
    {
        $result = World::call(
            "/countries",
            array_filter(compact('fields'))
        );

        if (is_null($result)) {
            return $result;
        }

        return array_map(
            fn (array $v) => self::fromJson($v)->__setClient(World::getClient()),
            $result
        );
    }

    /*
     * Instance Endpoints
     */

    public function parent(?array $fields = null): ?static
    {
        if (isset($this->parent)) {
            return $this->parent;
        }

        $result = $this->client->makeSafeCall(
            !is_null($this->parent_id)
                ? "/division/{$this->parent_id}"
                : "/division/{$this->id}/parent",
            compact('fields')
        );

        if (is_null($result) || empty($result)) {
            return null;
        }

        $parent = self::fromJson($result);

        $parent->__setClient($this->client);

        $this->__setParent($parent);

        return $parent;
    }

    /**
     * @return Division[]|null
     */
    public function children(?array $fields = null): ?array
    {
        if (isset($this->children)) {
            return $this->children;
        }

        $result = $this->client->makeSafeCall("/division/{$this->id}/children", compact('fields'));

        if (!$result) {
            return null;
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
     * Castings
     */

    public static function fromJson(array|string $json): static
    {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }

        if (is_null($json)) {
            throw new InvalidArgumentException("Invalid JSON passed", 1);
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

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        $parent_id = isset($this->parent) ? $this->parent->id : $this->parent_id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'a1code' => $this->a1code,
            'level' => $this->level,
            'population' => $this->population,
            'lat' => $this->lat,
            'long' => $this->long,
            'timezone' => $this->timezone,
            ...array_filter(compact('parent_id'), fn ($v) => !is_null($v))
        ];
    }

    /*
     * Internal
     */

    public function __setParent(?Division $division): self
    {
        $this->parent = $division;

        return $this;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
