<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Next;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use UnexpectedValueException;

/**
 * Class ControllerMiddleware.
 *
 * This middleware acts as a proxy to allow the implementations of the controller
 * pattern in an application.
 *
 * It optionally takes an instance from ContainerInterface to resolve the
 * controller instance and it's arguments from there, if possible.
 *
 * You must know that this class makes heavy use of reflection, so it could
 * slow down you application a bit. Use judiciously.
 */
final class ControllerMiddleware implements MiddlewareInterface
{
    use ArgumentInjector;

    /**
     * @var class-string
     */
    private string $controllerClass;
    private string $method;

    /**
     * ControllerMiddleware constructor.
     *
     * @param class-string       $controllerClass
     * @param string             $method
     * @param ContainerInterface $container
     */
    public function __construct(string $controllerClass, string $method, ContainerInterface $container = null)
    {
        $this->controllerClass = $controllerClass;
        $this->method = $method;
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Next    $next
     *
     * @return Response
     *
     * @throws ReflectionException
     */
    public function process(Request $request, Next $next): Response
    {
        // We create the controller instance
        $controller = $this->makeControllerInstance($request, $next);

        // We resolve the controller method arguments
        $arguments = $this->resolveArguments(new ReflectionMethod($this->controllerClass, $this->method), $request, $next);

        // We call the controller methods passing the parameters
        $response = call_user_func_array([$controller, $this->method], $arguments);

        // We ensure that there is a proper response from the controller.
        if (!$response instanceof ResponseInterface) {
            throw new UnexpectedValueException(sprintf('Method %s::%s must return an instance of %s', $this->controllerClass, $this->method, ResponseInterface::class));
        }

        // We return that response
        return $response;
    }

    /**
     * @param Request $request
     * @param Next    $next
     *
     * @return object
     *
     * @throws ReflectionException
     */
    private function makeControllerInstance(Request $request, Next $next): object
    {
        if ($this->container !== null && $this->container->has($this->controllerClass)) {
            return $this->container->get($this->controllerClass);
        }

        try {
            $reflectionClass = new ReflectionClass($this->controllerClass);
        } catch (ReflectionException $e) {
            throw new \InvalidArgumentException(sprintf('Cannot reflect class %s', $this->controllerClass));
        }

        $constructorMethod = $reflectionClass->getConstructor();

        if ($constructorMethod === null) {
            return $reflectionClass->newInstance();
        }

        $constructorArguments = $this->resolveArguments($constructorMethod, $request, $next);

        return $reflectionClass->newInstance(...$constructorArguments);
    }

    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }

    public function getMethodName(): string
    {
        return $this->method;
    }
}
