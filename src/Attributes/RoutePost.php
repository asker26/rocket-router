<?php

namespace RocketRouter\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RoutePost
{
    public function __construct(
        public string $route = ''
    )
    {
    }
}