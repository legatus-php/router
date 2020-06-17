<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router;

use MNC\PathToRegExpPHP\PathRegExp;
use MNC\PathToRegExpPHP\PathRegExpFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Next;

/**
 * Class Route.
 *
 * @internal
 */
class Route extends Path
{
    use RoutingHelper;

    /**
     * @var string[]
     */
    private array $methods;

    /**
     * @param array               $methods
     * @param string              $path
     * @param MiddlewareInterface $middleware
     *
     * @return Route
     */
    public static function fromPath(array $methods, string $path, MiddlewareInterface $middleware): self
    {
        return new self($methods, PathRegExpFactory::create($path), $middleware);
    }

    /**
     * Route constructor.
     *
     * @param array               $methods
     * @param PathRegExp          $path
     * @param MiddlewareInterface $middleware
     */
    public function __construct(array $methods, PathRegExp $path, MiddlewareInterface $middleware)
    {
        $this->methods = $methods;
        parent::__construct($path, $middleware);
    }

    protected function postMatchingHook(Request $request, Next $next): ?Response
    {
        // If method does not match but the path does, then we save a method not allowed attr in the request
        if (!$this->methodMatches($request->getMethod())) {
            $methods = $request->getAttribute(LegatusRouter::METHOD_NOT_ALLOWED_ATTR, []);
            $methods = array_merge($methods, $this->methods);
            $request = $request->withAttribute(LegatusRouter::METHOD_NOT_ALLOWED_ATTR, $methods);

            return $next->handle($request);
        }

        return null;
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
