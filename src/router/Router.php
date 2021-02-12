<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Class Router.
 *
 * This Router is implemented as a middleware execution pipeline.
 */
class Router implements Handler
{
    /**
     * @var Middleware[]
     * @psalm-var array<int,Middleware>
     */
    private array $middleware;
    private Handler $fallbackHandler;

    /**
     * @return Router
     */
    public static function create(): Router
    {
        return new self();
    }

    /**
     * Router constructor.
     *
     * @param Handler|null $fallbackHandler
     * @param Middleware   ...$middleware
     */
    public function __construct(Handler $fallbackHandler = null, Middleware ...$middleware)
    {
        $this->fallbackHandler = $fallbackHandler ?? HandleNotFound::instance();
        $this->middleware = $middleware;
    }

    /**
     * @param Middleware $middleware
     *
     * @return static
     */
    public function use(Middleware $middleware): Router
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * @param Request $request
     *
     * @return ResponseInterface
     */
    public function handle(Request $request): ResponseInterface
    {
        try {
            $request = RoutingContext::inject($request);
        } catch (InvalidRoutingContextOverride $e) {
        }
        $handler = stack($this->fallbackHandler, ...$this->middleware);

        return $handler->handle($request);
    }

    /**
     * @param string  $path
     * @param Handler $handler
     *
     * @return static
     */
    public function get(string $path, Handler $handler): Router
    {
        $this->route(['GET', 'HEAD'], $path, $handler);

        return $this;
    }

    /**
     * @param string  $path
     * @param Handler $handler
     *
     * @return static
     */
    public function post(string $path, Handler $handler): Router
    {
        $this->route(['POST'], $path, $handler);

        return $this;
    }

    /**
     * @param string  $path
     * @param Handler $handler
     *
     * @return static
     */
    public function put(string $path, Handler $handler): Router
    {
        $this->route(['PUT'], $path, $handler);

        return $this;
    }

    /**
     * @param string  $path
     * @param Handler $handler
     *
     * @return static
     */
    public function patch(string $path, Handler $handler): Router
    {
        $this->route(['PATCH'], $path, $handler);

        return $this;
    }

    /**
     * @param string  $path
     * @param Handler $handler
     *
     * @return static
     */
    public function delete(string $path, Handler $handler): Router
    {
        $this->route(['DELETE'], $path, $handler);

        return $this;
    }

    /**
     * @param string  $path
     * @param Handler $handler
     *
     * @return static
     */
    public function options(string $path, Handler $handler): Router
    {
        $this->route(['OPTIONS'], $path, $handler);

        return $this;
    }

    /**
     * @param string[] $methods
     * @param string   $path
     * @param Handler  $handler
     *
     * @return static
     */
    public function route(array $methods, string $path, Handler $handler): Router
    {
        $this->use(Route::define($methods, $path, $handler));

        return $this;
    }

    /**
     * @param string  $path
     * @param Handler $handler
     *
     * @return Router
     */
    public function path(string $path, Handler $handler): Router
    {
        return $this->use(Path::define($path, $handler));
    }
}
