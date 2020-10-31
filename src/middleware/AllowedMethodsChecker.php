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
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * AllowedMethodsChecker throws an exception if the request matched a path
 * but no methods.
 *
 * It is implemented as a singleton since has no dependencies and can be used
 * in many routers. This is to save memory.
 *
 * It it also possible to create an instance of it.
 */
final class AllowedMethodsChecker implements MiddlewareInterface
{
    private static ?AllowedMethodsChecker $instance = null;

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
     * @param Handler $handler
     *
     * @return Response
     *
     * @throws MethodNotAllowed
     * @throws MissingRoutingContext
     */
    public function process(Request $request, Handler $handler): Response
    {
        $context = RoutingContext::of($request);
        if ($context->isMethodNotAllowed()) {
            throw new MethodNotAllowed($request, $context->getAllowedMethods());
        }

        return $handler->handle($request);
    }
}
