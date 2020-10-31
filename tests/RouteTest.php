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

class RouteTest extends TestCase
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

        $route = Route::define(['GET'], '/users', $innerHandlerStub);
        $this->expectException(MissingRoutingContext::class);
        $route->process($requestMock, $handlerStub);
    }

    public function testItCallsNextHandlerWhenPathDoesNotMatch(): void
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

        $route = Route::define(['GET'], '/users', $innerHandlerStub);

        $response = $route->process($requestMock, $handlerMock);
        self::assertSame($responseStub, $response);
    }

    public function testItCallsNextHandlerWhenMethodDoesNotMatch(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $innerHandlerStub = $this->createStub(RequestHandlerInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $routingContextMock = $this->createMock(RoutingContext::class);
        $resultsStub = $this->createStub(MatchResult::class);

        $requestMock->expects(self::once())
            ->method('getAttribute')
            ->with(RoutingContext::ATTR_NAME)
            ->willReturn($routingContextMock);
        $requestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $routingContextMock->expects(self::once())
            ->method('match')
            ->with(self::isInstanceOf(PathRegExp::class))
            ->willReturn($resultsStub);
        $routingContextMock->expects(self::once())
            ->method('saveAllowedMethod')
            ->with('GET');

        $handlerMock->expects(self::once())
            ->method('handle')
            ->with($requestMock)
            ->willReturn($responseStub);

        $route = Route::define(['GET'], '/users', $innerHandlerStub);

        $response = $route->process($requestMock, $handlerMock);
        self::assertSame($responseStub, $response);
    }

    public function testItCallsInnerHandlerUponSuccessfulMatch(): void
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
        $requestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('GET');

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

        $route = Route::define(['GET'], '/users', $innerHandlerMock);

        $response = $route->process($requestMock, $handlerStub);
        self::assertSame($responseStub, $response);
    }
}
