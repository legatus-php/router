<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Next;
use RuntimeException;

/**
 * Class ContainerMiddlewareAdapter.
 *
 * Adapts a container service to a MiddlewareInterface
 */
final class ContainerMiddlewareAdapter implements MiddlewareInterface
{
    private ContainerInterface $container;
    private string $serviceName;

    /**
     * ContainerMiddlewareAdapter constructor.
     *
     * @param ContainerInterface $container
     * @param string             $serviceName
     */
    public function __construct(ContainerInterface $container, string $serviceName)
    {
        $this->container = $container;
        $this->serviceName = $serviceName;
    }

    /**
     * @param Request $request
     * @param Next    $next
     *
     * @return Response
     */
    public function process(Request $request, Next $next): Response
    {
        return $this->getMiddleware()->process($request, $next);
    }

    /**
     * @return MiddlewareInterface
     */
    private function getMiddleware(): MiddlewareInterface
    {
        $middleware = $this->container->get($this->serviceName);

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        throw new RuntimeException(sprintf('Service "%s" is not a valid middleware instance', $this->serviceName));
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }
}
