<?php

namespace Legatus\Http\Router;

use Legatus\Http\MiddlewareQueue\Factory\ArrayQueueFactory;
use Legatus\Http\MiddlewareQueue\Factory\QueueFactory;
use Legatus\Http\Router\Resolver\ArgumentResolver;
use function Legatus\Http\Router\Resolver\create_default;

function create(object $closureThis = null, QueueFactory $queueFactory = null, ArgumentResolver $resolver = null): Router {
    $queueFactory = $queueFactory ?? new ArrayQueueFactory();
    $resolver =  $resolver ?? create_default($closureThis, $queueFactory);
    return new LegatusRouter($queueFactory->create(), $resolver);
}

namespace Legatus\Http\Router\Resolver;

use Legatus\Http\MiddlewareQueue\Factory\ArrayQueueFactory;
use Legatus\Http\MiddlewareQueue\Factory\QueueFactory;
use Psr\Container\ContainerInterface;

/**
 * @param object $closureThis
 * @param QueueFactory|null $factory
 * @return ArgumentResolver
 */
function create_default(object $closureThis = null, QueueFactory $factory = null): ArgumentResolver {
    $resolver = compose(
        request_handler(),
        closure($closureThis),
    );
    if ($factory !== null) {
        $resolver->setQueueFactory($factory);
    }
    return $resolver;
}

function compose(ArgumentResolver ...$resolvers): CompositeArgumentResolver {
    return new CompositeArgumentResolver(
        new ArrayQueueFactory(),
        ...$resolvers
    );
}

function request_handler(): RequestHandlerArgumentResolver {
    return new RequestHandlerArgumentResolver();
}

function closure(object $closureThis = null): ClosureArgumentResolver {
    return new ClosureArgumentResolver($closureThis);
}

function container(ContainerInterface $container, bool $lazy = true): ContainerArgumentResolver {
    return new ContainerArgumentResolver($container, $lazy);
}

function controller(string $ns, ContainerInterface $container = null, string $separator = '@'): ControllerArgumentResolver {
    return new ControllerArgumentResolver($ns, $container, $separator);
}