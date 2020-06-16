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
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Next;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionType;
use RuntimeException;

/**
 * Class ArgumentInjector.
 */
trait ArgumentInjector
{
    protected ?ContainerInterface $container;

    /**
     * @param ReflectionFunctionAbstract $method
     * @param Request                    $request
     * @param Next                       $next
     *
     * @return array
     *
     * @throws ReflectionException
     */
    private function resolveArguments(ReflectionFunctionAbstract $method, Request $request, Next $next): array
    {
        $reflectionParams = $method->getParameters();
        $resolvedParams = [];
        foreach ($reflectionParams as $reflectionParam) {
            $reflectionType = $reflectionParam->getType();

            // If we do not have type information
            if (!$reflectionType instanceof ReflectionType) {
                $attr = $this->searchNameInAttributes($reflectionParam->getName(), $request);
                // If the param is in the request attributes by name, we inject it.
                if ($attr !== null) {
                    $resolvedParams[] = $attr;
                    continue;
                }
                // If the param has a default value, we inject that
                if ($reflectionParam->isDefaultValueAvailable()) {
                    $resolvedParams[] = $reflectionParam->getDefaultValue();
                    continue;
                }
                // If none of these, we just cannot resolve it
                throw $this->unresolvable($method->getShortName(), $method->getName(), $reflectionParam);
            }

            $typeName = $reflectionType->getName();

            // If the type is the request, we inject it
            if ($typeName === 'Psr\Http\Message\ServerRequestInterface') {
                $resolvedParams[] = $request;
                continue;
            }
            // If the type is the request handler, we inject it
            if ($typeName === 'Psr\Http\Server\RequestHandlerInterface') {
                $resolvedParams[] = $next;
                continue;
            }

            // It the type is built in, we inject the attribute name
            if ($reflectionType->isBuiltin()) {
                $attr = $this->searchNameInAttributes($reflectionParam->getName(), $request);
                if ($attr !== null) {
                    $resolvedParams[] = $attr;
                    continue;
                }
            }

            // We search for an object of that type in the request attributes
            $object = $this->searchTypeInAttributes($typeName, $request);
            if (is_object($object)) {
                $resolvedParams[] = $object;
                continue;
            }

            // If none of these, we look in the container
            if ($this->container !== null && $this->container->has($typeName)) {
                $resolvedParams[] = $this->container->get($typeName);
                continue;
            }

            // If the param has a default value, we inject that
            if ($reflectionParam->isDefaultValueAvailable()) {
                $resolvedParams[] = $reflectionParam->getDefaultValue();
                continue;
            }

            // If the type allows null, we pass null
            if ($reflectionType->allowsNull()) {
                $resolvedParams[] = null;
                continue;
            }

            throw $this->unresolvable($method->getShortName(), $method->getName(), $reflectionParam);
        }

        return $resolvedParams;
    }

    /**
     * @param string  $type
     * @param Request $request
     *
     * @return mixed|null
     */
    private function searchTypeInAttributes(string $type, Request $request)
    {
        // We only search for classes or interfaces
        if (class_exists($type) === true || interface_exists($type) === true) {
            $attributes = $request->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute instanceof $type) {
                    return $attribute;
                }
            }
        }

        return null;
    }

    /**
     * @param string  $name
     * @param Request $request
     *
     * @return mixed|null
     */
    private function searchNameInAttributes(string $name, Request $request)
    {
        return $request->getAttribute($name);
    }

    /**
     * @param string              $class
     * @param string              $methodName
     * @param ReflectionParameter $param
     *
     * @return RuntimeException
     */
    private function unresolvable(string $class, string $methodName, ReflectionParameter $param): RuntimeException
    {
        $type = $param->getType();

        $position = $param->getPosition() + 1;
        $suffix = 'th';
        if ($position === 1) {
            $suffix = 'st';
        }
        if ($position === 2) {
            $suffix = 'nd';
        }
        if ($position === 3) {
            $suffix = 'rd';
        }

        $paramText = sprintf('"$%s"', $param->getName());
        if ($type !== null) {
            $paramText = sprintf('%s (%s)', $paramText, $type->getName());
        }

        $template = 'Could not resolve %s%s parameter %s for method "%s" in controller %s. Try ';
        $message = sprintf($template, $position, $suffix, $paramText, $methodName, $class);

        if ($type === null) {
            $message .= 'typing the parameter name or ';
        }
        if ($this->container === null) {
            $message .= 'using a dependency injection container (adding the parameter as a service) or ';
        }
        $message .= 'adding the parameter to the request attributes.';

        return new RuntimeException($message);
    }
}
