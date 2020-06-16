<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router;

use MNC\PathToRegExpPHP\NoMatchException;
use MNC\PathToRegExpPHP\PathRegExp;
use MNC\PathToRegExpPHP\PathRegExpFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Next;

/**
 * Class Path.
 *
 * Path allows to prepend middleware with a path and extract it if matches.
 *
 * @internal
 */
class Path implements MiddlewareInterface
{
    use RoutingHelper;

    /**
     * @var PathRegExp
     */
    protected $path;
    /**
     * @var MiddlewareInterface
     */
    protected $middleware;

    /**
     * @param string              $path
     * @param MiddlewareInterface $middleware
     *
     * @return Path
     */
    public static function fromString(string $path, MiddlewareInterface $middleware): Path
    {
        return new self(PathRegExpFactory::create($path, 0), $middleware);
    }

    /**
     * Route constructor.
     *
     * @param PathRegExp          $path
     * @param MiddlewareInterface $middleware
     */
    public function __construct(PathRegExp $path, MiddlewareInterface $middleware)
    {
        $this->path = $path;
        $this->middleware = $middleware;
    }

    /**
     * @param Request $request
     * @param Next    $next
     *
     * @return Response
     */
    public function process(Request $request, Next $next): Response
    {
        // We get the routing uri to match
        $uri = $this->getRoutingUri($request);

        // We fix the trailing slash if missing
        if (substr($uri->getPath(), -1) !== '/') {
            $uri = $uri->withPath($uri->getPath().'/');
            $request->withUri($uri);
        }

        $path = $uri->getPath();

        // We try to match the path
        try {
            $result = $this->path->match($path);
        } catch (NoMatchException $e) {
            return $next->handle($request);
        }

        $response = $this->postMatchingHook($request, $next);

        if ($response instanceof Response) {
            return $response;
        }

        // If it matches, we create a new path in the request
        $newPath = str_replace($result->getMatchedString(), '', $uri->getPath());
        // We save the routing uri
        $request = $this->setRoutingUri($request, $uri->withPath($newPath));
        // We save the match result
        $request = $request->withAttribute(QuiltRouter::MATCH_RESULT, $result);

        // We inject the matched params if any
        foreach ($result->getValues() as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $this->middleware->process($request, $next);
    }

    protected function postMatchingHook(Request $request, Next $next): ?Response
    {
        return null;
    }
}
