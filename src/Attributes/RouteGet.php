<?php

namespace RocketRouter\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteGet
{
    public function __construct(
        public string $route = ''
    )
    {
        
    }
}