<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RequestHandlerMiddlewareResolver.
 */
final class RequestHandlerMiddlewareResolver implements MiddlewareResolver
{
    /**
     * @param $any
     *
     * @return MiddlewareInterface
     */
    public function resolve($any): MiddlewareInterface
    {
        if ($any instanceof RequestHandlerInterface) {
            return new RequestHandlerMiddlewareAdapter($any);
        }
        throw new InvalidArgumentException(sprintf('argument is not an instance of %s', RequestHandlerInterface::class));
    }
}
