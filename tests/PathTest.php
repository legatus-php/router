<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use MNC\PathToRegExpPHP\MatchResult;
use MNC\PathToRegExpPHP\NoMatchException;
use MNC\PathToRegExpPHP\PathRegExp;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PathTest extends TestCase
{
    public function testItThrowsMissingContext(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $handlerStub = $this->createStub(RequestHandlerInterface::class);
        $innerHandlerStub = $this->createStub(RequestHandlerInterface::class);

        $requestMock->expects(self::once())
            ->method('getAttribute')
            ->with(RoutingContext::ATTR_NAME)
            ->willReturn(null);

        $path = Path::define('/users', $innerHandlerStub);
        $this->expectException(MissingRoutingContext::class);
        $path->process($requestMock, $handlerStub);
    }

    public function testItMatchesPath(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $handlerStub = $this->createStub(RequestHandlerInterface::class);
        $innerHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $routingContextMock = $this->createMock(RoutingContext::class);
        $resultsStub = $this->createStub(MatchResult::class);

        $requestMock->expects(self::once())
            ->method('getAttribute')
            ->with(RoutingContext::ATTR_NAME)
            ->willReturn($routingContextMock);

        $routingContextMock->expects(self::once())
            ->method('match')
            ->with(self::isInstanceOf(PathRegExp::class))
            ->willReturn($resultsStub);
        $routingContextMock->expects(self::once())
            ->method('storeMatchResult')
            ->with($resultsStub);
        $innerHandlerMock->expects(self::once())
            ->method('handle')
            ->with($requestMock)
            ->willReturn($responseStub);

        $path = Path::define('/users', $innerHandlerMock);

        $response = $path->process($requestMock, $handlerStub);
        self::assertSame($responseStub, $response);
    }

    public function testItCallsNextHandlerWhenDoesNotMatch(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $innerHandlerStub = $this->createStub(RequestHandlerInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $routingContextMock = $this->createMock(RoutingContext::class);

        $requestMock->expects(self::once())
            ->method('getAttribute')
            ->with(RoutingContext::ATTR_NAME)
            ->willReturn($routingContextMock);

        $routingContextMock->expects(self::once())
            ->method('match')
            ->with(self::isInstanceOf(PathRegExp::class))
            ->willThrowException(new NoMatchException());
        $handlerMock->expects(self::once())
            ->method('handle')
            ->with($requestMock)
            ->willReturn($responseStub);

        $path = Path::define('/users', $innerHandlerStub);

        $response = $path->process($requestMock, $handlerMock);
        self::assertSame($responseStub, $response);
    }
}
