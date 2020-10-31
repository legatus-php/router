<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Class CallableMiddleware.
 */
final class CallableMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    private $middleware;

    /**
     * CallableMiddleware constructor.
     *
     * @param callable $middleware
     */
    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @param Request $request
     * @param Handler $handler
     *
     * @return Response
     */
    public function process(Request $request, Handler $handler): Response
    {
        return ($this->middleware)($request, $handler);
    }
}
