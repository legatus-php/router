<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Tests;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouterFunctionalTest extends TestCase
{
    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    protected function handle(ServerRequestInterface $request): ResponseInterface
    {
        return TestRouterConfigurator::configure()->handle($request);
    }

    public function testItReachesHomeView(): void
    {
        $request = new ServerRequest('GET', '/');
        $response = $this->handle($request);
        $this->assertSame('Home View', (string) $response->getBody());
        $this->assertSame('text/html', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItReachesLoginForm(): void
    {
        $request = new ServerRequest('GET', '/login');
        $response = $this->handle($request);
        $this->assertSame('Login Form', (string) $response->getBody());
        $this->assertSame('text/html', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItRedirectsFromLogin(): void
    {
        $request = new ServerRequest('POST', '/login');
        $response = $this->handle($request);
        $this->assertSame('/', $response->getHeaderLine('Location'));
        $this->assertSame('text/html', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testItReachesCoursesView(): void
    {
        $request = new ServerRequest('GET', '/courses');
        $response = $this->handle($request);
        $this->assertSame('Courses View', (string) $response->getBody());
        $this->assertSame('text/html', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItReachesApiUsersList(): void
    {
        $request = new ServerRequest('POST', '/api/v1/users');
        $response = $this->handle($request);
        $this->assertSame('', (string) $response->getBody());
        $this->assertSame('application/json', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItReachesApiUserById(): void
    {
        $request = new ServerRequest('GET', '/api/v1/users/22');
        $response = $this->handle($request);
        $this->assertSame('{"id":"22"}', (string) $response->getBody());
        $this->assertSame('application/json', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItReachesApiUserPurchases(): void
    {
        $request = new ServerRequest('GET', '/api/v1/users/44/purchases');
        $response = $this->handle($request);
        $this->assertSame('{"id":"44","purchases":[]}', (string) $response->getBody());
        $this->assertSame('application/json', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItDeletesUser(): void
    {
        $request = new ServerRequest('DELETE', '/api/v1/users/44');
        $response = $this->handle($request);
        $this->assertSame('', (string) $response->getBody());
        $this->assertSame('application/json', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testItPostsToAuthLogin(): void
    {
        $request = new ServerRequest('POST', '/api/v1/auth/login');
        $response = $this->handle($request);
        $this->assertSame('{"token":"token"}', (string) $response->getBody());
        $this->assertSame('application/json', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItGetsAuthMe(): void
    {
        $request = new ServerRequest('GET', '/api/v1/auth/me');
        $response = $this->handle($request);
        $this->assertSame('{"msg":"User data"}', (string) $response->getBody());
        $this->assertSame('application/json', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItHandlesApiErrors(): void
    {
        $request = new ServerRequest('GET', '/api/v1/auth/wrong-route');
        $response = $this->handle($request);
        $this->assertSame('{"msg":"Cannot GET \/api\/v1\/auth\/wrong-route"}', (string) $response->getBody());
        $this->assertSame('application/json', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testItHandlesNormalErrors(): void
    {
        $request = new ServerRequest('GET', '/wrong-route');
        $response = $this->handle($request);
        $this->assertSame('Cannot GET /wrong-route', (string) $response->getBody());
        $this->assertSame('text/html', (string) $response->getHeaderLine('Content-Type'));
        $this->assertSame(404, $response->getStatusCode());
    }
}
