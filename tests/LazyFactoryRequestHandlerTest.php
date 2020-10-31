<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class LazyFactoryRequestHandlerTest.
 */
class LazyFactoryRequestHandlerTest extends TestCase
{
    public function testItThrowsExceptionOnInvalidHandler(): void
    {
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $factory = static fn () => '';
        $handler = new LazyFactoryRequestHandler($factory);
        $this->expectException(InvalidArgumentException::class);
        $handler->handle($requestStub);
    }

    public function testItCallsTheInnerHandler(): void
    {
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $factory = static fn () => $handlerMock;

        $handlerMock->expects(self::once())
            ->method('handle')
            ->with($requestStub)
            ->willReturn($responseStub);

        $handler = new LazyFactoryRequestHandler($factory);
        $response = $handler->handle($requestStub);
        self::assertSame($responseStub, $response);
    }
}
