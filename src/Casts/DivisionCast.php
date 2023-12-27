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

        if (!$this->isInt($value)) {
            return null;
        }

        return Division::get($value);
    }

    public function set($model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value) || $this->isInt($value)) {
            return $value;
        }

        if (!$value instanceof Division) {
            throw new InvalidArgumentException('The given value is not a Division instance nor ID.');
        }

        return $value->id;
    }

    /**
     * Checks if value is integer and converts it from a numeric string
     */
    protected function isInt(mixed &$value): bool
    {
        if (is_integer($value)) {
            return true;
        }

        if (!is_numeric($value)) {
            return false;
        }

        preg_match("/^\d+$/", trim($value), $match);

        if (isset($match[0])) {
            $value = intval($match[0]);
            return true;
        }

        return false;
    }
}
