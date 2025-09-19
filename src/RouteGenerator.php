<?php

declare(strict_types=1);

namespace RocketRouter;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/**
 * Route Cache Generator
 * Scans for classes with #[ApiController] attribute and generates route mappings
 */
class RouteGenerator
{
    private array $routes = [];
    
    /**
     * Generate routes from a directory and save to output file
     */
    public function generate(string $searchDirectory, string $outputFile): array
    {
        $this->routes = [];
        
        if (!is_dir($searchDirectory)) {
            throw new \InvalidArgumentException("Directory does not exist: {$searchDirectory}");
        }
        
        $outputDir = dirname($outputFile);
        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
            throw new RuntimeException("Cannot create output directory: {$outputDir}");
        }
        
        echo "Scanning for API controllers in: {$searchDirectory}\n";
        $phpFiles = $this->scanDirectory($searchDirectory);
        
        foreach ($phpFiles as $file) {
            $this->processFile($file);
        }
        
        $this->generateCacheFile($outputFile);
        
        echo "\nRoute cache generated successfully!\n";
        echo "Total routes: " . count($this->routes) . "\n";
        echo "Cache file: {$outputFile}\n";
        
        return $this->routes;
    }
    
    /**
     * Recursively scan directory for PHP files
     */
    private function scanDirectory(string $dir): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Process a single PHP file for routes
     */
    private function processFile(string $filePath): void
    {
        $classInfo = $this->getClassInfo($filePath);
        
        if (empty($classInfo['class']) || !$this->hasApiControllerAttribute($classInfo['content'])) {
            return;
        }
        
        echo "Found API Controller: {$classInfo['fullClass']}\n";
        
        $baseRoute = $this->getBaseRoute($classInfo['content']);
        $methodRoutes = $this->getMethodRoutes($classInfo['content']);
        
        foreach ($methodRoutes as $methodRoute) {
            $fullRoute = $this->buildFullRoute($baseRoute, $methodRoute['route']);
            
            $this->routes[] = [
                'route' => $fullRoute,
                'method' => $methodRoute['method'],
                'controller' => $classInfo['fullClass'],
                'function' => $methodRoute['function']
            ];
            
            echo "  - {$methodRoute['method']} /{$fullRoute} -> {$methodRoute['function']}()\n";
        }
    }
    
    /**
     * Extract namespace and class name from a PHP file
     */
    private function getClassInfo(string $filePath): array
    {
        $content = file_get_contents($filePath);
        
        // Extract namespace
        $namespace = '';
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = trim($matches[1]);
        }
        
        // Extract class name
        $className = '';
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            $className = $matches[1];
        }
        
        return [
            'namespace' => $namespace,
            'class' => $className,
            'fullClass' => $namespace ? $namespace . '\\' . $className : $className,
            'content' => $content
        ];
    }
    
    /**
     * Check if class has ApiController attribute
     */
    private function hasApiControllerAttribute(string $content): bool
    {
        return (bool) preg_match('/#\[ApiController]/', $content);
    }
    
    /**
     * Extract base route from Route attribute
     */
    private function getBaseRoute(string $content): string
    {
        if (preg_match('/#\[Route\([\'"]([^\'"]+)[\'"]\)]/', $content, $matches)) {
            return $matches[1];
        }
        return '';
    }
    
    /**
     * Extract method routes from content
     */
    private function getMethodRoutes(string $content): array
    {
        $methods = [];
        
        // Split content into lines for better parsing
        $lines = explode("\n", $content);
        $currentAttributes = [];

        foreach ($lines as $iValue) {
            $line = trim($iValue);
            
            // Check for route attributes
            if (preg_match('/#\[(Route(?:Get|Post|Delete|Put|Patch))(?:\([\'"]([^\'"]*)[\'"]?\))?]/', $line, $matches)) {
                $attributeType = $matches[1];
                $route = $matches[2] ?? '';
                
                // Determine HTTP method
                $httpMethod = match ($attributeType) {
                    'RouteGet' => 'GET',
                    'RoutePost' => 'POST',
                    'RouteDelete' => 'DELETE',
                    'RoutePut' => 'PUT',
                    'RoutePatch' => 'PATCH',
                    default => 'GET'
                };
                
                $currentAttributes = [
                    'method' => $httpMethod,
                    'route' => $route
                ];
            }
            
            // Check for function definition
            if (preg_match('/public\s+function\s+(\w+)/', $line, $matches) && !empty($currentAttributes)) {
                $functionName = $matches[1];
                
                $methods[] = [
                    'method' => $currentAttributes['method'],
                    'route' => $currentAttributes['route'],
                    'function' => $functionName
                ];
                
                $currentAttributes = []; // Reset for next method
            }
        }

        return $methods;
    }
    
    /**
     * Build a full route from base route and method route
     */
    private function buildFullRoute(string $baseRoute, string $methodRoute): string
    {
        $fullRoute = $baseRoute;
        if (!empty($methodRoute)) {
            $fullRoute .= '/' . $methodRoute;
        }
        
        $fullRoute = trim($fullRoute, '/');

        return preg_replace('/\/+/', '/', $fullRoute);
    }
    
    /**
     * Generate the cache file content
     */
    private function generateCacheFile(string $outputFile): void
    {
        $cacheContent = "<?php\n\n";
        $cacheContent .= "/**\n";
        $cacheContent .= " * Auto-generated route cache\n";
        $cacheContent .= " * Generated on: " . date('Y-m-d H:i:s') . "\n";
        $cacheContent .= " */\n\n";
        $cacheContent .= "return " . var_export($this->routes, true) . ";\n";
        
        if (file_put_contents($outputFile, $cacheContent) === false) {
            throw new RuntimeException("Failed to write cache file: {$outputFile}");
        }
    }
    
    /**
     * Display generated routes for verification
     */
    public function displayRoutes(): void
    {
        echo "\nGenerated routes:\n";
        foreach ($this->routes as $route) {
            echo "  {$route['method']} /{$route['route']} -> {$route['controller']}::{$route['function']}()\n";
        }
    }
}
