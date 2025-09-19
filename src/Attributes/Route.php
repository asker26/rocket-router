<?php

namespace RocketRouter\Attributes;

use Attribute;

#[Attribute( Attribute::TARGET_CLASS )]
class Route
{
    public function __construct(
        public string $route
    )
    {
    }
}