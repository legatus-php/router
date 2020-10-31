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
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HandleNotFound simply throws an exception if executed.
 *
 * This is usually implemented as the last handler in routers.
 */
final class HandleNotFound implements RequestHandlerInterface
{
    private static ?HandleNotFound $instance = null;

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
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new NotFound($request);
    }
}
