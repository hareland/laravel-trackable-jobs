<?php

namespace Hareland\Trackable\Casts;

use Hareland\Trackable\Enums\Status;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


class StatusCast implements CastsAttributes
{
    /**
     * Get from string
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_string($value)) {
            return Status::tryFrom($value);
        }
        return $value;
    }

    /**
     * Get from enum
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof Status) {
            return $value->value;
        }
        return $value;
    }
}