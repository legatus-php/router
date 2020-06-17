<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Resolver;

use Closure;
use Legatus\Http\Router\Middleware\ClosureMiddlewareAdapter;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class ClosureArgumentResolver.
 */
final class ClosureArgumentResolver implements ArgumentResolver
{
    private ?object $closureThis;

    /**
     * ClosureArgumentResolver constructor.
     *
     * @param object|null $closureThis
     */
    public function __construct(object $closureThis = null)
    {
        $this->closureThis = $closureThis;
    }

    /**
     * @param $any
     *
     * @return MiddlewareInterface
     */
    public function resolve($any): MiddlewareInterface
    {
        if (is_callable($any) && !$any instanceof Closure) {
            $any = Closure::fromCallable($any);
        }
        if (!$any instanceof Closure) {
            throw new UnresolvableArgument('Argument is not a closure');
        }

        if ($this->closureThis !== null) {
            $any = $any->bindTo($this->closureThis);
        }

        return new ClosureMiddlewareAdapter($any);
    }
}
