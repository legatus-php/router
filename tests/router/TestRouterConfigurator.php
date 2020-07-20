<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Server\RequestHandlerInterface as Next;

/**
 * Class TestRouterConfigurator.
 */
class TestRouterConfigurator implements RouterConfigurator
{
    /**
     * @return Router
     */
    public static function configure(): Router
    {
        $router = create_router();
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
            ->nested('/api/v1', [$this, 'apiRoutes'])
            ->use([$this, 'htmlErrorHandler'])
            ->get('/', function () {
                return $this->html('Home View');
            })
            ->get('/login', function () {
                return $this->html('Login Form');
            })
            ->post('/login', function () {
                return $this->redirect('/');
            })
            ->get('/courses', function () {
                return $this->html('Courses View');
            })
            ->stop();
    }

    /**
     * @param Router $router
     */
    public function apiRoutes(Router $router): void
    {
        $router
            ->use([$this, 'apiErrorHandler'])
            ->nested('/users', [$this, 'userRoutes'])
            ->nested('/auth', [$this, 'authRoutes'])
            ->stop();
    }

    /**
     * @param Router $router
     */
    public function userRoutes(Router $router): void
    {
        $router
            ->post('/', function () {
                return $this->json([]);
            })
            ->get('/:id', function (string $id) {
                return $this->json(['id' => $id]);
            })
            ->get('/:id/purchases', function (string $id) {
                return $this->json(['id' => $id, 'purchases' => []]);
            })
            ->delete('/:id', function (string $id) {
                return $this->json(null, 204);
            })
            ->stop();
    }

    /**
     * @param Router $router
     */
    public function authRoutes(Router $router): void
    {
        $router
            ->post('/login', function () {
                return $this->json(['token' => 'token']);
            })
            ->get('/me', function () {
                return $this->json(['msg' => 'User data']);
            })
            ->stop();
    }

    /**
     * @param Req  $req
     * @param Next $next
     *
     * @return ResponseInterface
     */
    public function htmlErrorHandler(Req $req, Next $next): ResponseInterface
    {
        try {
            return $next->handle($req);
        } catch (HttpError $exception) {
            return $this->html($exception->getMessage())
                ->withStatus($exception->getCode());
        }
    }

    /**
     * @param Req  $req
     * @param Next $next
     *
     * @return ResponseInterface
     */
    public function apiErrorHandler(Req $req, Next $next): ResponseInterface
    {
        try {
            return $next->handle($req);
        } catch (HttpError $exception) {
            return $this->json(['msg' => $exception->getMessage()])
                ->withStatus($exception->getCode());
        }
    }

    /**
     * @param string $body
     * @param int    $status
     *
     * @return ResponseInterface
     */
    protected function response(string $body, int $status = 200): ResponseInterface
    {
        return new Response($status, [], $body);
    }

    /**
     * @param array $data
     * @param int   $status
     *
     * @return ResponseInterface
     */
    protected function json(array $data = null, int $status = 200): ResponseInterface
    {
        return $this->response($data ? json_encode($data) : '', $status)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param string $html
     * @param int    $status
     *
     * @return ResponseInterface
     */
    protected function html(string $html, int $status = 200): ResponseInterface
    {
        return $this->response($html, $status)
            ->withHeader('Content-Type', 'text/html');
    }

    /**
     * @param string $uri
     *
     * @return ResponseInterface
     */
    protected function redirect(string $uri): ResponseInterface
    {
        return $this->html('', 302)
            ->withHeader('Location', $uri);
    }
}
