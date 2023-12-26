<?php

namespace WeblaborMx\World;

abstract class Entity
{
    protected Client $client;

    abstract public static function fromJson(array|string $json): static;

    public function __setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
