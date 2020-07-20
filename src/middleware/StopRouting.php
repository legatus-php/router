<?php

declare(strict_types=1);

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
use Psr\Http\Server\RequestHandlerInterface as Next;

/**
 * Class MethodNotAllowedMiddleware.
 *
 * This middleware throws exceptions when the request comes.
 */
final class StopRouting implements MiddlewareInterface
{
    private static ?StopRouting $instance = null;

    /**
     * @return static
     */
    public static function instance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param Request $request
     * @param Next    $next
     *
     * @return Response
     *
     * @throws MethodNotAllowed
     * @throws NotFound
     */
    public function process(Request $request, Next $next): Response
    {
        if (Router::isMethodNotAllowed($request)) {
            throw new MethodNotAllowed($request, Router::getAllowedMethods($request));
        }

        throw new NotFound($request);
    }
}
