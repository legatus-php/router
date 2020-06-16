<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Interface Router.
 *
 * Defines the contract for an object that has the capabilities of a router.
 *
 * Routes and middleware are run in the order declared. Keep that in mind when
 * building your router instances.
 */
interface Router extends MiddlewareInterface, RequestHandlerInterface
{
    /**
     * Registers a middleware to be run.
     *
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable $middleware
     *
     * @return Router
     */
    public function use($middleware): Router;

    /**
     * Registers a GET route handler.
     *
     * @param string                                                      $path       The route path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler The route handler
     *
     * @return Router
     */
    public function get(string $path, ...$handler): Router;

    /**
     * Registers a POST route handler.
     *
     * @param string                                                      $path       The route path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler The route handler
     *
     * @return Router
     */
    public function post(string $path, ...$handler): Router;

    /**
     * Registers a PUT route handler.
     *
     * @param string                                                      $path       The route path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler The route handler
     *
     * @return Router
     */
    public function put(string $path, ...$handler): Router;

    /**
     * Registers a PATCH route handler.
     *
     * @param string                                                      $path       The route path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler The route handler
     *
     * @return Router
     */
    public function patch(string $path, ...$handler): Router;

    /**
     * Registers a DELETE route handler.
     *
     * @param string                                                      $path       The route path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler The route handler
     *
     * @return Router
     */
    public function delete(string $path, ...$handler): Router;

    /**
     * Registers an OPTIONS route handler.
     *
     * @param string                                                      $path       The route path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler The route handler
     *
     * @return Router
     */
    public function options(string $path, ...$handler): Router;

    /**
     * Registers a route handler.
     *
     * @param array                                                       $methods    The http methods supported
     * @param string                                                      $path       The route path
     * @param MiddlewareInterface|RequestHandlerInterface|string|callable ...$handler The route handler
     *
     * @return Router
     */
    public function route(array $methods, string $path, ...$handler): Router;

    /**
     * Creates a nested router under a path.
     *
     * @param string                      $path
     * @param callable|RouterConfigurator $configurator
     *
     * @return Router
     */
    public function nested(string $path, callable $configurator): Router;

    /**
     * Stops the routing for this router.
     *
     * @return Router
     */
    public function stop(): Router;
}
