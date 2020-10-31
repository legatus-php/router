<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class MiddlewareRequestHandler.
 */
final class MiddlewareRequestHandler implements RequestHandlerInterface
{
    private MiddlewareInterface $middleware;
    private RequestHandlerInterface $next;

    /**
     * MiddlewareRequestHandler constructor.
     *
     * @param MiddlewareInterface     $middleware
     * @param RequestHandlerInterface $next
     */
    public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $next)
    {
        $this->middleware = $middleware;
        $this->next = $next;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->next);
    }
}
