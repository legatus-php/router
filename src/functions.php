<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Psr\Container\ContainerInterface;

/**
 * @param object|null             $closureThis
 * @param ContainerInterface|null $container
 *
 * @return Router
 *
 * @deprecated Use Router::create() instead
 */
function create_router(object $closureThis = null, ContainerInterface $container = null): Router
{
    $queueFactory = new ArrayMiddlewareQueueFactory();
    $resolvers = new CompositeMiddlewareResolver(
        $queueFactory,
        new RequestHandlerMiddlewareResolver(),
        new ClosureMiddlewareResolver($closureThis ?? $container),
    );
    if ($container !== null) {
        $resolvers->push(new ContainerMiddlewareResolver($container));
    }

    return new Router($queueFactory->create(), $resolvers);
}
