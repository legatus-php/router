<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Resolver;

use Legatus\Http\Router\Middleware\ControllerMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class ControllerMiddlewareFactory.
 */
final class ControllerArgumentResolver implements ArgumentResolver
{
    /**
     * @var string
     */
    private $baseNamespace;
    /**
     * @var ContainerInterface|null
     */
    private $container;
    /**
     * @var string
     */
    private $separator;

    /**
     * ControllerMiddlewareFactory constructor.
     *
     * @param string             $baseNamespace
     * @param ContainerInterface $container
     * @param string             $separator
     */
    public function __construct(string $baseNamespace, ContainerInterface $container = null, string $separator = '@')
    {
        $this->baseNamespace = $baseNamespace;
        $this->container = $container;
        $this->separator = $separator;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($any): MiddlewareInterface
    {
        if (!is_string($any) || strpos($any, $this->separator) === false) {
            throw new UnresolvableArgument('Argument is not a string that complies with the controller specification');
        }
        [$className, $method] = explode($this->separator, $any, 2);

        $fqcn = $this->baseNamespace.'\\'.$className;

        if (!class_exists($fqcn)) {
            throw new UnresolvableArgument(sprintf('Argument specified controller class (%s) does not exist', $fqcn));
        }

        return new ControllerMiddleware($fqcn, $method, $this->container);
    }
}
