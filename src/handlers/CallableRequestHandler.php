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
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CallableRequestHandler proxies the handle function to the passed
 * callable.
 *
 * The passed callable MUST have the same signature.
 *
 * You might want to use the factory function instead of this class directly.
 *
 * @see \Legatus\Http\handle_func
 */
final class CallableRequestHandler implements RequestHandlerInterface
{
    /**
     * @var callable
     */
    private $handler;

    /**
     * CallableRequestHandler constructor.
     *
     * @param callable $handler
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request): Response
    {
        return ($this->handler)($request);
    }
}
