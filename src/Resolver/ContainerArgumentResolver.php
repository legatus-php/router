<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Resolver;

use Legatus\Http\Router\Middleware\ContainerMiddlewareAdapter;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class ContainerArgumentResolver.
 */
final class ContainerArgumentResolver implements ArgumentResolver
{
    private ContainerInterface $container;
    private bool $lazy;

    /**
     * ContainerArgumentResolver constructor.
     *
     * @param ContainerInterface $container
     * @param bool               $lazy
     */
    public function __construct(ContainerInterface $container, bool $lazy = true)
    {
        $this->container = $container;
        $this->lazy = $lazy;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($any): MiddlewareInterface
    {
        if (\is_string($any) && $this->container->has($any)) {
            return $this->createMiddleware($any);
        }
        throw new UnresolvableArgument(sprintf('Service "%s" does not exist in the container', $any));
    }

    protected function createMiddleware(string $any): MiddlewareInterface
    {
        if ($this->lazy === true) {
            return new ContainerMiddlewareAdapter($this->container, $any);
        }

        return $this->container->get($any);
    }
}
