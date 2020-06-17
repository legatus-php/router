<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router;

use Legatus\Http\MiddlewareQueue\Queue;
use Legatus\Http\MiddlewareQueue\QueueMiddleware;
use Legatus\Http\Router\Middleware\StopRouting;
use Legatus\Http\Router\Resolver\ArgumentResolver;
use MNC\PathToRegExpPHP\PathRegExpFactory;

/**
 * Class LegatusRouter.
 *
 * This LegatusRouter is implemented as a middleware execution pipeline
 *
 * It is faster than the routers that perform collection pattern matching when
 * used the right way, but unfortunately you loose the benefit of building
 * routes based on their names.
 */
class LegatusRouter extends QueueMiddleware implements Router
{
    public const ROUTING_URI_ATTR = '__routing_uri';
    public const METHOD_NOT_ALLOWED_ATTR = '__method_not_allowed';
    public const MATCH_RESULT = '__match_result';

    private ArgumentResolver $resolver;

    /**
     * LegatusRouter constructor.
     *
     * @param ArgumentResolver $resolver
     * @param Queue            $queue
     */
    public function __construct(Queue $queue, ArgumentResolver $resolver)
    {
        parent::__construct($queue);
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function use($middleware): Router
    {
        $this->push($this->resolver->resolve($middleware));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path, ...$handler): Router
    {
        $this->route(['GET'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $path, ...$handler): Router
    {
        $this->route(['POST'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, ...$handler): Router
    {
        $this->route(['PUT'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $path, ...$handler): Router
    {
        $this->route(['PATCH'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path, ...$handler): Router
    {
        $this->route(['DELETE'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function options(string $path, ...$handler): Router
    {
        $this->route(['OPTIONS'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function route(array $methods, string $path, ...$handler): Router
    {
        $this->use(Route::fromPath($methods, $path, $this->resolver->resolve($handler)));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function nested(string $path, $configurator): Router
    {
        $router = new self($this->emptyQueue(), $this->resolver);
        $configurator($router);
        $subPath = new Path(PathRegExpFactory::create($path, 0), $router);
        $this->use($subPath);

        return $this;
    }

    /**
     * @return Router
     */
    public function stop(): Router
    {
        $this->use(StopRouting::instance());

        return $this;
    }
}
