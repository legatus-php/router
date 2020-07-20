<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Closure;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Next;
use ReflectionFunction;
use RuntimeException;

/**
 * Class CallableMiddlewareAdapte.
 *
 * Adapts a Closure to a MiddlewareInterface
 */
final class ClosureMiddlewareAdapter implements MiddlewareInterface
{
    use ArgumentInjector;

    /**
     * @var Closure
     */
    private Closure $closure;

    /**
     * CallableMiddlewareAdapter constructor.
     *
     * @param Closure $callable $callable
     */
    public function __construct(Closure $callable)
    {
        $this->closure = $callable;
    }

    public function process(Request $request, Next $next): Response
    {
        $reflectionFunction = new ReflectionFunction($this->closure);
        $arguments = $this->resolveArguments($reflectionFunction, $request, $next);
        $response = ($this->closure)(...$arguments);
        if ($response instanceof Response) {
            return $response;
        }

        throw new RuntimeException(sprintf('Callable provided in middleware does not return a %s instance', Response::class));
    }

    /**
     * @return Closure
     */
    public function getClosure(): Closure
    {
        return $this->closure;
    }
}
