<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Resolver;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Interface ArgumentResolver.
 */
interface ArgumentResolver
{
    /**
     * @param $any
     *
     * @return MiddlewareInterface
     *
     * @throws UnresolvableArgument when the argument cannot be resolved
     */
    public function resolve($any): MiddlewareInterface;
}
