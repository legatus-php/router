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

/**
 * Interface MiddlewareResolver.
 */
interface MiddlewareResolver
{
    /**
     * @param $any
     *
     * @return MiddlewareInterface
     *
     * @throws InvalidArgumentException when the argument passed cannot be
     *                                  resolved into a middleware instance
     */
    public function resolve($any): MiddlewareInterface;
}
