<?php

declare(strict_types=1);

namespace RocketRouter\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class RoutePost extends HttpMethod
{
    public function __construct(
        public string $route = ''
    )
    {
        parent::__construct('POST', $route);
    }
}