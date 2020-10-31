<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class PlainTextErrorHandlerTest.
 */
class PlainTextErrorHandlerTest extends TestCase
{
    public function testItDoesNotHandleException(): void
    {
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $responseFactoryStub = $this->createStub(ResponseFactoryInterface::class);

        $handlerMock->expects(self::once())
            ->method('handle')
            ->with($requestStub)
            ->willReturn($responseStub);

        $errorHandler = new PlainTextErrorHandler($responseFactoryStub);
        $response = $errorHandler->process($requestStub, $handlerMock);

        self::assertSame($responseStub, $response);
    }

    public function testItHandlesHttpError(): void
    {
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);

        $handlerMock->expects(self::once())
            ->method('handle')
            ->with($requestStub)
            ->willThrowException(new NotFound($requestStub, 'Error'));

        $responseFactoryMock->expects(self::once())
            ->method('createResponse')
            ->with(404)
            ->willReturn($responseMock);

        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);
        $streamMock->expects(self::once())
            ->method('write');
        $responseMock->expects(self::exactly(2))
            ->method('withHeader')
            ->willReturn($responseMock);

        $errorHandler = new PlainTextErrorHandler($responseFactoryMock);
        $response = $errorHandler->process($requestStub, $handlerMock);

        self::assertSame($responseMock, $response);
    }

    public function testItHandlesNormalError(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $uriMock = $this->createMock(UriInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);

        $handlerMock->expects(self::once())
            ->method('handle')
            ->with($requestMock)
            ->willThrowException(new \InvalidArgumentException('Bla bla bla'));

        $requestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($uriMock);
        $uriMock->expects(self::once())
            ->method('getPath')
            ->willReturn('/some/path');
        $requestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('GET');

        $responseFactoryMock->expects(self::once())
            ->method('createResponse')
            ->with(500)
            ->willReturn($responseMock);

        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);
        $streamMock->expects(self::once())
            ->method('write');
        $responseMock->expects(self::exactly(2))
            ->method('withHeader')
            ->willReturn($responseMock);

        $errorHandler = new PlainTextErrorHandler($responseFactoryMock);
        $response = $errorHandler->process($requestMock, $handlerMock);

        self::assertSame($responseMock, $response);
    }
}
