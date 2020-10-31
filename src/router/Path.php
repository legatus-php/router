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
 * Class Path.
 *
 * Path allows to prepend middleware with a path and extract it if matches.
 *
 * @internal
 */
class Path implements MiddlewareInterface
{
    protected PathRegExp $path;
    protected Handler $handler;

    /**
     * @param string  $path
     * @param Handler $handler
     *
     * @return Path
     */
    public static function define(string $path, Handler $handler): Path
    {
        return new self(PathRegExpFactory::create($path, 0), $handler);
    }

    /**
     * Route constructor.
     *
     * @param PathRegExp $path
     * @param Handler    $handler
     */
    protected function __construct(PathRegExp $path, Handler $handler)
    {
        $this->path = $path;
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

        $context->storeMatchResult($result);

        return $this->handler->handle($request);
    }
}
