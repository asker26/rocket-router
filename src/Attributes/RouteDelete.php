<?php

namespace RocketRouter\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteDelete
{
    public function __construct(
        public string $route = ''
    )
    {
    }
}