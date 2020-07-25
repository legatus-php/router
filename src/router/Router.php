<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use MNC\PathToRegExpPHP\MatchResult;
use MNC\PathToRegExpPHP\PathRegExpFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Router.
 *
 * This Router is implemented as a middleware execution pipeline
 *
 * It is faster than the routers that perform collection pattern matching when
 * used the right way, but unfortunately you loose the benefit of building
 * routes based on their names.
 */
class Router extends QueueMiddleware
{
    private const URI_ATTR = 'router.uri';
    private const ALLOWED_METHODS_ATTR = 'router.allowed_methods';
    private const MATCH_RESULT_ATTR = 'router.match_result';

    /**
     * Check if the request matched a path but not a method.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function isMethodNotAllowed(Request $request): bool
    {
        return $request->getAttribute(self::ALLOWED_METHODS_ATTR) !== null;
    }

    /**
     * Returns the allowed methods for the matched path.
     *
     * @param Request $request
     *
     * @return array
     */
    public static function getAllowedMethods(Request $request): array
    {
        return $request->getAttribute(self::ALLOWED_METHODS_ATTR, []);
    }

    /**
     * @param Request $request
     * @param array   $methods
     *
     * @return Request
     */
    public static function addAllowedMethods(Request $request, array $methods): Request
    {
        $internalMethods = $request->getAttribute(self::ALLOWED_METHODS_ATTR, []);
        $internalMethods = array_merge($internalMethods, $methods);

        return $request->withAttribute(self::ALLOWED_METHODS_ATTR, $internalMethods);
    }

    /**
     * @param Request      $request
     * @param UriInterface $uri
     *
     * @return Request
     */
    public static function setUriToMatch(Request $request, UriInterface $uri): Request
    {
        return $request->withAttribute(self::URI_ATTR, $uri);
    }

    /**
     * @param Request $request
     *
     * @return UriInterface
     */
    public static function getUriToMatch(Request $request): UriInterface
    {
        $uri = $request->getAttribute(self::URI_ATTR);
        if ($uri instanceof UriInterface) {
            return $uri;
        }

        return $request->getUri();
    }

    /**
     * @param Request     $request
     * @param MatchResult $matchResult
     *
     * @return Request
     */
    public static function setMatchResult(Request $request, MatchResult $matchResult): Request
    {
        return $request->withAttribute(self::MATCH_RESULT_ATTR, $matchResult);
    }

    private MiddlewareResolver $resolver;

    /**
     * Router constructor.
     *
     * @param MiddlewareResolver $resolver
     * @param MiddlewareQueue    $queue
     */
    public function __construct(MiddlewareQueue $queue, MiddlewareResolver $resolver)
    {
        parent::__construct($queue);
        $this->resolver = $resolver;
    }

    /**
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$middleware
     *
     * @return static
     */
    public function use(...$middleware): Router
    {
        $this->push($this->resolver->resolve($middleware));

        return $this;
    }

    /**
     * @param string                                                      $path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler
     *
     * @return static
     */
    public function get(string $path, ...$handler): Router
    {
        $this->route(['GET'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * @param string                                                      $path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler
     *
     * @return static
     */
    public function post(string $path, ...$handler): Router
    {
        $this->route(['POST'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * @param string                                                      $path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler
     *
     * @return static
     */
    public function put(string $path, ...$handler): Router
    {
        $this->route(['PUT'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * @param string                                                      $path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler
     *
     * @return static
     */
    public function patch(string $path, ...$handler): Router
    {
        $this->route(['PATCH'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * @param string                                                      $path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler
     *
     * @return static
     */
    public function delete(string $path, ...$handler): Router
    {
        $this->route(['DELETE'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * @param string                                                      $path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler
     *
     * @return static
     */
    public function options(string $path, ...$handler): Router
    {
        $this->route(['OPTIONS'], $path, $this->resolver->resolve($handler));

        return $this;
    }

    /**
     * @param string[]                                                    $methods
     * @param string                                                      $path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler
     *
     * @return static
     */
    public function route(array $methods, string $path, ...$handler): Router
    {
        $this->use(Route::fromPath($methods, $path, $this->resolver->resolve($handler)));

        return $this;
    }

    /**
     * @param string                      $path
     * @param RouterConfigurator|callable $configurator
     *
     * @return Router
     */
    public function nested(string $path, $configurator): Router
    {
        $router = new self($this->queue->copy(), $this->resolver);
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