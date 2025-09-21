<?php

declare(strict_types=1);

namespace RocketRouter\Attributes;

/**
 * @internal
 */
class HttpMethod
{
    public function __construct(
        public string $method,
        public string $route = ''
    )
    {
    }
}