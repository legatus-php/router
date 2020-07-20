<?php

namespace Legatus\Http;

/**
 * @param object|null $closureThis
 * @return Router
 */
function create_router(object $closureThis = null): Router {
    $queueFactory = new ArrayMiddlewareQueueFactory();
    $resolver =  new CompositeMiddlewareResolver(
        $queueFactory,
        new RequestHandlerMiddlewareResolver(),
        new ClosureMiddlewareResolver($closureThis),
    );
    return new Router($queueFactory->create(), $resolver);
}