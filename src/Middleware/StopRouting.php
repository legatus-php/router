<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Middleware;

use Legatus\Http\Errors\MethodNotAllowedHttpError;
use Legatus\Http\Errors\NotFoundHttpError;
use Legatus\Http\Router\RoutingHelper;
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
    use RoutingHelper;

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
     * @throws MethodNotAllowedHttpError
     * @throws NotFoundHttpError
     */
    public function process(Request $request, Next $next): Response
    {
        if ($this->isMethodNotAllowed($request)) {
            throw new MethodNotAllowedHttpError($request, $this->getAllowedMethods($request));
        }

        throw new NotFoundHttpError($request);
    }
}
