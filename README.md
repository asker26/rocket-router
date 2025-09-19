# RocketRouter

A fast and flexible PHP router package for building web applications and APIs.

## Installation

Install via Composer:

```bash
composer require asker/rocket-router
```

## Usage

### Basic Usage

```php
<?php

require_once 'vendor/autoload.php';

use RocketRouter\Router;

$router = new Router();

// Define routes
$router->get('/', function() {
    return 'Hello World!';
});

$router->post('/api/users', function() {
    return json_encode(['message' => 'User created']);
});

// Dispatch the request
echo $router->dispatch();
```

### Available Methods

- `get(string $path, callable $handler)` - Add a GET route
- `post(string $path, callable $handler)` - Add a POST route
- `addRoute(string $method, string $path, callable $handler)` - Add a route for any HTTP method
- `dispatch()` - Process the current request and return the response

## Requirements

- PHP 8.0 or higher

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
