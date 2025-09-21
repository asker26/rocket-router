<?php

declare(strict_types=1);

namespace RocketRouter;

use Closure;
use Composer\Autoload\ClassLoader;

final class RouterBuilder
{
    private string $projectDir = '';

    private ?string $cacheFile = null;
    private ?Closure $serviceLocator = null;

    private ?Closure $routeRegisterer = null;

    private ClassLoader $loader;

    public function setProjectDir(string $projectDir): RouterBuilder
    {
        $this->projectDir = $projectDir;
        return $this;
    }

    public function setCacheFile(string $cacheFile): RouterBuilder
    {
        $this->cacheFile = $cacheFile;
        return $this;
    }

    public function setServiceLocator(Closure $serviceLocator): RouterBuilder
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function setRouteRegisterer(Closure $routeRegisterer): RouterBuilder
    {
        $this->routeRegisterer = $routeRegisterer;
        return $this;
    }

    public function setLoader(ClassLoader $loader): RouterBuilder
    {
        $this->loader = $loader;
        return $this;
    }

    public function build(): Router
    {
        return new Router(
            $this->projectDir,
            $this->serviceLocator,
            $this->routeRegisterer,
            $this->loader,
            $this->cacheFile
        );
    }
}