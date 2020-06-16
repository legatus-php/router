<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Resolver;

use Legatus\Http\MiddlewareQueue\Factory\QueueFactory;
use Legatus\Http\MiddlewareQueue\QueueMiddleware;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class CompositeArgumentResolver.
 */
final class CompositeArgumentResolver implements ArgumentResolver
{
    /**
     * @var ArgumentResolver[]
     */
    private $resolvers;
    /**
     * @var QueueFactory
     */
    private $queueFactory;

    /**
     * CompositeArgumentResolver constructor.
     *
     * @param QueueFactory     $queueFactory
     * @param ArgumentResolver ...$resolvers
     */
    public function __construct(QueueFactory $queueFactory, ArgumentResolver ...$resolvers)
    {
        $this->queueFactory = $queueFactory;
        $this->resolvers = $resolvers;
    }

    /**
     * @param QueueFactory $queueFactory
     */
    public function setQueueFactory(QueueFactory $queueFactory): void
    {
        $this->queueFactory = $queueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($any): MiddlewareInterface
    {
        // Step 1: Recursively create middleware
        if (\is_array($any) && !\is_callable($any)) {
            if (\count($any) === 0) {
                throw new UnresolvableArgument('You must provide at least one element when passing an array');
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
            } catch (UnresolvableArgument $exception) {
                $errors[] = $exception->getMessage();
                continue;
            }
        }

        throw new UnresolvableArgument(sprintf('Could not resolve argument. Reasons: %s', implode('. ', $errors)));
    }
}
