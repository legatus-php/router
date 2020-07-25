Legatus Router
==============

A fast and composable middleware router inspired in Express.js

[![Type Coverage](https://shepherd.dev/github/legatus-php/router/coverage.svg)](https://shepherd.dev/github/legatus-php/router)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Flegatus-php%2Frouter%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/legatus-php/router/master)

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

# Project status & release process

While this library is still under development, it is well tested and should be stable enough to use in production environments.

The current releases are numbered 0.x.y. When a non-breaking change is introduced (adding new methods, optimizing existing code, etc.), y is incremented.

When a breaking change is introduced, a new 0.x version cycle is always started.

It is therefore safe to lock your project to a given release cycle, such as 0.2.*.

If you need to upgrade to a newer release cycle, check the [release history][releases] for a list of changes introduced by each further 0.x.0 version.

## Community
We still do not have a community channel. If you would like to help with that, you can let me know!

## Contributing
Read the contributing guide to know how can you contribute to Legatus.

## Security Issues
Please report security issues privately by email and give us a period of grace before disclosing.

## About Legatus
Legatus is a personal open source project led by Matías Navarro Carter and developed by contributors.

[composer]: https://getcomposer.org/
[docs]: https://legatus.dev/components/router
[releases]: https://github.com/legatus-php/router/releases