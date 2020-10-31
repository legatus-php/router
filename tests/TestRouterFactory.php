<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Psr\Http\Message\ServerRequestInterface as Req;

/**
 * Class TestRouterConfigurator.
 */
class TestRouterFactory
{
    /**
     * @return Router
     */
    public static function configure(): Router
    {
        $router = new Router();
        $configurator = new self();
        $configurator($router);

        return $router;
    }

    /**
     * @param Router $router
     */
    public function __invoke(Router $router): void
    {
        $router
            ->path('/api/v1', $this->createApiRouter())
            ->use(middle_func('handle_error_html'))
            ->get('/', handle_func(fn () => html('Home View')))
            ->get('/login', handle_func(fn () => html('Login Form')))
            ->post('/login', handle_func(fn () => redirect('/')))
            ->get('/courses', handle_func(fn () => html('Courses View')));
    }

    /**
     * @return Router
     */
    protected function createApiRouter(): Router
    {
        return Router::create()
            ->use(middle_func('handle_error_json'))
            ->path('/users', $this->userRouter())
            ->path('/auth', $this->authRouter());
    }

    /**
     * @return Router
     */
    public function userRouter(): Router
    {
        return Router::create()
            ->post('/', handle_func(fn () => json([])))
            ->get('/:id', handle_func(fn (Req $req) => json(['id' => RoutingContext::of($req)->getParam('id')])))
            ->get('/:id/purchases', handle_func(fn (Req $req) => json(['id' => RoutingContext::of($req)->getParam('id'), 'purchases' => []])))
            ->delete('/:id', handle_func(fn () => json(null, 204)));
    }

    /**
     * @return Router
     */
    public function authRouter(): Router
    {
        return Router::create()
            ->post('/login', handle_func(fn () => json(['token' => 'token'])))
            ->get('/me', handle_func(fn () => json(['msg' => 'User data'])));
    }
}
