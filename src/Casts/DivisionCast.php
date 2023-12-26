<?php

namespace WeblaborMx\World\Casts;

use InvalidArgumentException;
use WeblaborMx\World\Entities\Division;

class DivisionCast
{
    public function get($model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return $value;
        }

        return Division::get($value);
    }

    public function set($model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value) || is_integer($value)) {
            return $value;
        }

        if (!$value instanceof Division) {
            throw new InvalidArgumentException('The given value is not a Division instance nor ID.');
        }

        return $value->id;
    }
}
