<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class LazyFactoryRequestHandler.
 */
final class LazyFactoryRequestHandler implements RequestHandlerInterface
{
    private const ERROR = '$createHandler callable passed to %s::__construct() must return an instance of %s.';

    /**
     * @var callable
     */
    private $createHandler;

    /**
     * LazyFactoryRequestHandler constructor.
     *
     * @param callable $createHandler A function that must return a RequestHandlerInterface
     */
    public function __construct(callable $createHandler)
    {
        $this->createHandler = $createHandler;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = ($this->createHandler)();
        if (!$handler instanceof RequestHandlerInterface) {
            $message = sprintf(self::ERROR, __CLASS__, RequestHandlerInterface::class);
            throw new InvalidArgumentException($message);
        }

        return $handler->handle($request);
    }
}
