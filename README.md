Legatus Router
==============

A fast and composable middleware router inspired in Express.js

[![Build Status](https://drone.mnavarro.dev/api/badges/legatus/router/status.svg)](https://drone.mnavarro.dev/legatus/router)

## Installation
You can install the Router component using [Composer][composer]:

```bash
composer require legatus/router
```

## Quick Start

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Server\RequestHandlerInterface as Next;

$router = Legatus\Http\create_router();

$router->use(static function (Req $req, Next $next) {
    // Do something with the request in this middleware
    return $next->handle($req);
});

$router->get('/users/:id', static function (string $id) {
    // Create a response in this route handler
    return new Nyholm\Psr7\Response(200, [], 'Hello User ' . $id);
});

// Its highly recommended to stop the routing at the end
$router->stop();

$request = new Nyholm\Psr7\ServerRequest('GET', '/users/1');
$response = $router->handle($request);

echo $response->getBody() . PHP_EOL; // Hello User 1
```

For more details you can check the [online documentation here][docs].

## Community
We still do not have a community channel. If you would like to help with that, you can let me know!

## Contributing
Read the contributing guide to know how can you contribute to Quilt.

## Security Issues
Please report security issues privately by email and give us a period of grace before disclosing.

## About Legatus
Legatus is a personal open source project led by Matías Navarro Carter and developed by contributors.

[composer]: https://getcomposer.org/
[docs]: https://legatus.mnavarro.dev/components/router