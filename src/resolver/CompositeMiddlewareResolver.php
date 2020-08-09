<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class CompositeMiddlewareResolver.
 */
final class CompositeMiddlewareResolver implements MiddlewareResolver
{
    /**
     * @var MiddlewareResolver[]
     */
    private array $resolvers;
    private MiddlewareQueueFactory $queueFactory;

    /**
     * CompositeMiddlewareResolver constructor.
     *
     * @param MiddlewareQueueFactory $queueFactory
     * @param MiddlewareResolver     ...$resolvers
     */
    public function __construct(MiddlewareQueueFactory $queueFactory, MiddlewareResolver ...$resolvers)
    {
        $this->queueFactory = $queueFactory;
        $this->resolvers = $resolvers;
    }

    /**
     * @param MiddlewareQueueFactory $queueFactory
     */
    public function setQueueFactory(MiddlewareQueueFactory $queueFactory): void
    {
        $this->queueFactory = $queueFactory;
    }

    /**
     * @param MiddlewareResolver $resolver
     */
    public function push(MiddlewareResolver $resolver): void
    {
        $this->resolvers[] = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($any): MiddlewareInterface
    {
        // Step 1: Recursively create middleware
        if (\is_array($any) && !\is_callable($any)) {
            if (\count($any) === 0) {
                throw new InvalidArgumentException('Empty array was provided');
            }
            if (\count($any) === 1) {
                return $this->resolve($any[0]);
            }
            $queue = new QueueMiddleware($this->queueFactory->create());
            foreach ($any as $single) {
                $queue->push($this->resolve($single));
            }

            return $queue;
        }

        // Step 2: If PSR middleware, then we just return
        if ($any instanceof MiddlewareInterface) {
            return $any;
        }

        // Step 3: Now we use the configured resolvers
        $errors = [sprintf('Not a %s instance', MiddlewareInterface::class)];

        foreach ($this->resolvers as $resolver) {
            try {
                return $resolver->resolve($any);
            } catch (InvalidArgumentException $exception) {
                $errors[] = $exception->getMessage();
                continue;
            }
        }

        throw new InvalidArgumentException(sprintf('Could not resolve argument. Maybe because %s', implode(' or ', $errors)));
    }
}
