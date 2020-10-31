<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Creates a PSR-15 RequestHandler by stacking multiple Middleware on top of a
 * RequestHandler.
 *
 * The Middleware is stacked in a FIFO fashion.
 *
 * @param Handler    $handler       The final handler to execute
 * @param Middleware ...$middleware The middleware in normal order of execution
 *
 * @return Handler The composed handler
 */
function stack(Handler $handler, Middleware ...$middleware): Handler
{
    return array_reduce(
        array_reverse($middleware),
        static fn (Handler $passed, Middleware $middleware) => new MiddlewareRequestHandler($middleware, $passed),
        $handler
    );
}

/**
 * Transforms any callable into a PSR-15 Request Handler.
 *
 * The passed callable MUST implement the handler signature correctly, ie,
 * it MUST take a PSR-7 Server Request as the first and only argument, and it
 * MUST return a PSR-7 Response.
 *
 * @param callable $callable
 *
 * @return Handler
 */
function handle_func(callable $callable): Handler
{
    return new CallableRequestHandler($callable);
}

/**
 * Transforms any callable into a PSR-15 Middleware.
 *
 * The passed callable MUST implement the middleware signature correctly, ie,
 * it MUST take a PSR-7 Server Request as the first argument and a PSR-15 Request
 * Handler as the second, and it MUST return a PSR-7 Response.
 *
 * @param callable $callable
 *
 * @return Middleware
 */
function middle_func(callable $callable): Middleware
{
    return new CallableMiddleware($callable);
}
