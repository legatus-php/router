<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Server\RequestHandlerInterface;

class PathTest extends TestCase
{
    public function testMatchesPath(): void
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('GET', '/users/211421412');
        $handler = $this->createStub(RequestHandlerInterface::class);

        $path = Path::fromString('/users', new CallableTestMiddleware(function (Req $req) use ($factory) {
            $this->assertSame('/211421412/', Router::getUriToMatch($req)->getPath());

            return $factory->createResponse(200)
                ->withBody($factory->createStream('OK'));
        }));

        $response = $path->process($request, $handler);
        self::assertSame('OK', (string) $response->getBody());
    }

    public function testMatchesDynamicPath(): void
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('GET', '/client/auth/211421412');
        $handler = $this->createStub(RequestHandlerInterface::class);

        $path = Path::fromString('/:slug', new CallableTestMiddleware(function (Req $req) use ($factory) {
            $this->assertSame('client', $req->getAttribute('slug'));
            $this->assertSame('/auth/211421412/', Router::getUriToMatch($req)->getPath());

            return $factory->createResponse(200)
                ->withBody($factory->createStream('OK'));
        }));

        $response = $path->process($request, $handler);
        self::assertSame('OK', (string) $response->getBody());
    }
}
