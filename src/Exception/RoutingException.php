<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Exception;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

/**
 * Class RoutingException.
 */
abstract class RoutingException extends RuntimeException
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * RoutingException constructor.
     *
     * @param ServerRequestInterface $request
     * @param int                    $code
     * @param Throwable|null         $previous
     */
    public function __construct(ServerRequestInterface $request, int $code, Throwable $previous = null)
    {
        $message = sprintf('Cannot %s %s',
            $request->getMethod(),
            $request->getUri()->getPath()
        );
        parent::__construct($message, $code, $previous);
        $this->request = $request;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
