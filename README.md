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

$queue = new Legatus\Http\MiddlewareQueue\ArrayQueue();
$queue->push(new SomeMiddleware());
$queue->push(new SomeOtherMiddleware());

$queue->handle($request);

// Or use the queue as a middleware
$queueMiddleware = new Legatus\Http\MiddlewareQueue\QueueMiddleware($queue);

$queueMiddleware->process($request, $handler);
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