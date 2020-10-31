<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use MNC\PathToRegExpPHP\NoMatchException;
use MNC\PathToRegExpPHP\PathRegExp;
use MNC\PathToRegExpPHP\PathRegExpFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Class Route.
 *
 * @internal
 */
class Route implements MiddlewareInterface
{
    protected PathRegExp $path;
    /**
     * @var string[]
     */
    private array $methods;
    protected Handler $handler;

    /**
     * @param array   $methods
     * @param string  $path
     * @param Handler $handler
     *
     * @return Route
     */
    public static function define(array $methods, string $path, Handler $handler): Route
    {
        return new self($methods, PathRegExpFactory::create($path), $handler);
    }

    /**
     * Route constructor.
     *
     * @param array      $methods
     * @param PathRegExp $path
     * @param Handler    $handler
     */
    protected function __construct(array $methods, PathRegExp $path, Handler $handler)
    {
        $this->path = $path;
        $this->methods = $methods;
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @param Handler $handler
     *
     * @return Response
     *
     * @throws MissingRoutingContext
     */
    public function process(Request $request, Handler $handler): Response
    {
        $context = RoutingContext::of($request);

        try {
            $result = $context->match($this->path);
        } catch (NoMatchException $e) {
            return $handler->handle($request);
        }

        if (!$this->methodMatches($request->getMethod())) {
            $context->saveAllowedMethod(...$this->methods);

            return $handler->handle($request);
        }

        $context->storeMatchResult($result);

        return $this->handler->handle($request);
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    protected function methodMatches(string $method): bool
    {
        return \in_array($method, $this->methods, true);
    }
}
