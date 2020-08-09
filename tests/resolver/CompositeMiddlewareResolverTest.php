<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Stub\Exception;
use PHPUnit\Framework\MockObject\Stub\ReturnArgument;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class CompositeMiddlewareResolverTest.
 */
class CompositeMiddlewareResolverTest extends TestCase
{
    public function testItResolvesNormalMiddlewareInstance(): void
    {
        $middlewareFactoryMock = $this->createMock(MiddlewareQueueFactory::class);
        $middlewareStub = $this->createStub(MiddlewareInterface::class);
        $resolver = new CompositeMiddlewareResolver($middlewareFactoryMock);

        self::assertSame($middlewareStub, $resolver->resolve($middlewareStub));
    }

    public function testItResolvesMultipleMiddlewareInstances(): void
    {
        $middlewareFactoryMock = $this->createMock(MiddlewareQueueFactory::class);
        $middlewareQueueMock = $this->createMock(MiddlewareQueue::class);
        $middlewareStubOne = $this->createStub(MiddlewareInterface::class);
        $middlewareStubTwo = $this->createStub(MiddlewareInterface::class);

        $middlewareFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($middlewareQueueMock);

        $middlewareQueueMock->expects(self::exactly(2))
            ->method('push')
            ->withConsecutive([$middlewareStubOne], [$middlewareStubTwo]);

        $resolver = new CompositeMiddlewareResolver($middlewareFactoryMock);

        self::assertInstanceOf(QueueMiddleware::class, $resolver->resolve([$middlewareStubOne, $middlewareStubTwo]));
    }

    public function testItResolvesMultipleArgumentsUsingVariousResolvers(): void
    {
        self::markTestIncomplete();
        $middlewareFactoryMock = $this->createMock(MiddlewareQueueFactory::class);
        $middlewareQueueMock = $this->createMock(MiddlewareQueue::class);
        $resolverOneMock = $this->createMock(MiddlewareResolver::class);
        $resolverTwoMock = $this->createMock(MiddlewareResolver::class);

        $middlewareStubArgThree = $this->createStub(MiddlewareInterface::class);

        $argOne = $this->createStub(MiddlewareInterface::class);
        $argTwo = 'some-service-name';
        $argThree = fn () => 'hello-world';

        $resolverOneMock->expects(self::exactly(2))
            ->method('resolve')
            ->withConsecutive([$argTwo, $argThree])
            ->willReturnOnConsecutiveCalls(
                new Exception(new InvalidArgumentException()),
                new ReturnArgument($middlewareStubArgThree)
            );

        $resolver = new CompositeMiddlewareResolver($middlewareFactoryMock, $resolverOneMock, $resolverTwoMock);

        $queue = $resolver->resolve([$argOne, $argTwo, $argThree]);

        self::assertInstanceOf(QueueMiddleware::class, $queue);
    }
}
